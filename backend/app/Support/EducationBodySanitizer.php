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
