<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class EducationBodySanitizer
{
	/** @var list<string> */
	private const ALLOWED = [
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		'p', 'ul', 'ol', 'li', 'br', 'strong', 'b', 'em', 'i',
	];

	/** @var list<string> */
	private const TEXT_ALIGN = ['left', 'right', 'center', 'justify'];

	/** @var list<string> */
	private const INLINE_BLOCK_PARENTS = [
		'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'td', 'th',
	];

	/** @var list<string> */
	private const REMOVE_ENTIRELY = [
		'script', 'style', 'iframe', 'object', 'embed', 'link', 'meta',
		'img', 'a', 'video', 'audio', 'form', 'input', 'button', 'svg',
	];

	public function sanitize(?string $html): ?string
	{
		if ($html === null) {
			return null;
		}

		$html = trim($html);
		if ($html === '') {
			return '';
		}

		$document = new DOMDocument('1.0', 'UTF-8');
		libxml_use_internal_errors(true);
		$document->loadHTML(
			'<?xml encoding="utf-8" ?><div>'.$html.'</div>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
		);
		libxml_clear_errors();

		$root = $document->documentElement;
		if (! $root instanceof DOMElement) {
			return '';
		}

		$this->cleanNode($root);

		$output = '';
		foreach ($root->childNodes as $child) {
			$output .= $document->saveHTML($child);
		}

		return trim($output) ?: '';
	}

	private function cleanNode(DOMNode $node): void
	{
		if (! $node->hasChildNodes()) {
			return;
		}

		/** @var list<DOMNode> $children */
		$children = [];
		foreach ($node->childNodes as $child) {
			$children[] = $child;
		}

		foreach ($children as $child) {
			if (! $child instanceof DOMElement) {
				continue;
			}

			$tag = strtolower($child->tagName);

			if (in_array($tag, self::REMOVE_ENTIRELY, true)) {
				$child->parentNode?->removeChild($child);

				continue;
			}

			if (! in_array($tag, self::ALLOWED, true)) {
				if ($tag === 'span' && $this->replaceSpanWithSemantic($child)) {
					$this->cleanNode($node);

					continue;
				}

				if ($tag === 'div') {
					$this->convertDivLineBreak($child);
					$this->cleanNode($node);

					continue;
				}

				$this->unwrapElement($child);
				$this->cleanNode($node);

				continue;
			}

			$this->filterAttributes($child);

			$this->cleanNode($child);
		}
	}

	private function filterAttributes(DOMElement $element): void
	{
		$textAlign = null;

		if ($element->hasAttribute('style')) {
			$textAlign = $this->extractTextAlign($element->getAttribute('style'));
		}

		if ($element->hasAttribute('align')) {
			$align = strtolower(trim($element->getAttribute('align')));
			if (in_array($align, self::TEXT_ALIGN, true)) {
				$textAlign ??= $align;
			}
		}

		while ($element->attributes->length > 0) {
			$attribute = $element->attributes->item(0);
			if ($attribute !== null) {
				$element->removeAttribute($attribute->name);
			}
		}

		if ($textAlign !== null) {
			$element->setAttribute('style', 'text-align: '.$textAlign);
		}
	}

	private function extractTextAlign(string $style): ?string
	{
		if (preg_match('/text-align\s*:\s*(left|right|center|justify)/i', $style, $matches) !== 1) {
			return null;
		}

		return strtolower($matches[1]);
	}

	private function replaceSpanWithSemantic(DOMElement $span): bool
	{
		$style = $span->getAttribute('style');
		$bold = $this->isBoldStyle($style);
		$italic = $this->isItalicStyle($style);

		if (! $bold && ! $italic) {
			return false;
		}

		$document = $span->ownerDocument;
		if ($document === null) {
			return false;
		}

		$replacement = $document->createElement($bold && $italic ? 'strong' : ($bold ? 'strong' : 'em'));

		if ($bold && $italic) {
			$em = $document->createElement('em');
			while ($span->firstChild !== null) {
				$em->appendChild($span->firstChild);
			}
			$replacement->appendChild($em);
		} else {
			while ($span->firstChild !== null) {
				$replacement->appendChild($span->firstChild);
			}
		}

		$span->parentNode?->replaceChild($replacement, $span);

		return true;
	}

	private function isBoldStyle(string $style): bool
	{
		return preg_match('/font-weight\s*:\s*(bold|[6-9]00)/i', $style) === 1;
	}

	private function isItalicStyle(string $style): bool
	{
		return preg_match('/font-style\s*:\s*italic/i', $style) === 1;
	}

	private function convertDivLineBreak(DOMElement $div): void
	{
		$parent = $div->parentNode;
		$parentTag = $parent instanceof DOMElement
			? strtolower($parent->tagName)
			: null;

		if ($parentTag !== null && in_array($parentTag, self::INLINE_BLOCK_PARENTS, true)) {
			$document = $div->ownerDocument;
			if ($document === null) {
				return;
			}

			$break = $document->createElement('br');
			while ($div->firstChild !== null) {
				$parent->insertBefore($div->firstChild, $div);
			}
			$parent->insertBefore($break, $div);
			$parent->removeChild($div);

			return;
		}

		$document = $div->ownerDocument;
		if ($document === null) {
			return;
		}

		$paragraph = $document->createElement('p');
		while ($div->firstChild !== null) {
			$paragraph->appendChild($div->firstChild);
		}

		if (! $paragraph->hasChildNodes()) {
			$paragraph->appendChild($document->createElement('br'));
		}

		$div->parentNode?->replaceChild($paragraph, $div);
	}

	private function unwrapElement(DOMElement $element): void
	{
		$parent = $element->parentNode;
		if ($parent === null) {
			return;
		}

		while ($element->firstChild !== null) {
			$parent->insertBefore($element->firstChild, $element);
		}

		$parent->removeChild($element);
	}
}
