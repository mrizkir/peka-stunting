<?php

namespace Tests\Unit;

use App\Support\UploadSizeLimit;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UploadSizeLimitTest extends TestCase
{
	#[DataProvider('iniSizeProvider')]
	public function test_parse_ini_size(string $input, int $expected): void
	{
		$this->assertSame($expected, UploadSizeLimit::parseIniSize($input));
	}

	public static function iniSizeProvider(): array
	{
		return [
			'2 megabytes' => ['2M', 2 * 1024 * 1024],
			'512 kilobytes' => ['512K', 512 * 1024],
			'1 gigabyte' => ['1G', 1024 * 1024 * 1024],
			'plain bytes' => ['2048', 2048],
			'unlimited' => ['-1', PHP_INT_MAX],
		];
	}

	public function test_format_bytes_uses_megabytes_for_large_values(): void
	{
		$this->assertSame('2 MB', UploadSizeLimit::formatBytes(2 * 1024 * 1024));
	}

	public function test_poster_image_max_label_is_human_readable(): void
	{
		$label = UploadSizeLimit::posterImageMaxLabel();

		$this->assertMatchesRegularExpression('/\d/', $label);
		$this->assertStringContainsString('MB', $label);
	}
}
