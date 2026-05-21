<?php

namespace Tests\Unit;

use App\Support\EducationBodySanitizer;
use PHPUnit\Framework\TestCase;

class EducationBodySanitizerTest extends TestCase
{
	private EducationBodySanitizer $sanitizer;

	protected function setUp(): void
	{
		parent::setUp();
		$this->sanitizer = new EducationBodySanitizer;
	}

	public function test_allows_headings_and_lists(): void
	{
		$html = '<h1>Judul</h1><p>Paragraf</p><ul><li>A</li></ul><ol><li>B</li></ol>';

		$result = $this->sanitizer->sanitize($html);

		$this->assertStringContainsString('<h1>Judul</h1>', $result);
		$this->assertStringContainsString('<ul>', $result);
		$this->assertStringContainsString('<ol>', $result);
	}

	public function test_removes_script_and_attributes(): void
	{
		$html = '<h2 onclick="alert(1)">Aman</h2><script>alert(1)</script>';

		$result = $this->sanitizer->sanitize($html);

		$this->assertStringContainsString('<h2>Aman</h2>', $result);
		$this->assertStringNotContainsString('script', $result);
		$this->assertStringNotContainsString('onclick', $result);
	}

	public function test_removes_images_and_links(): void
	{
		$html = '<p>Teks</p><img src="/x.png"><a href="https://evil.test">link</a>';

		$result = $this->sanitizer->sanitize($html);

		$this->assertStringContainsString('<p>Teks</p>', $result);
		$this->assertStringNotContainsString('<img', $result);
		$this->assertStringNotContainsString('<a', $result);
	}

	public function test_keeps_text_align_style_only(): void
	{
		$html = '<p style="text-align: center; color: red; font-size: 99px">Tengah</p>';

		$result = $this->sanitizer->sanitize($html);

		$this->assertStringContainsString('text-align: center', $result);
		$this->assertStringNotContainsString('color', $result);
		$this->assertStringNotContainsString('font-size', $result);
	}

	public function test_converts_align_attribute_to_style(): void
	{
		$html = '<h2 align="right">Judul</h2>';

		$result = $this->sanitizer->sanitize($html);

		$this->assertStringContainsString('text-align: right', $result);
		$this->assertStringNotContainsString('align=', $result);
	}
}
