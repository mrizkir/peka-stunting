<?php

namespace Tests\Unit;

use App\Support\EducationVideoUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EducationVideoUrlTest extends TestCase
{
	#[DataProvider('youtubeUrlProvider')]
	public function test_extracts_youtube_video_id(string $url, ?string $expectedId): void
	{
		$this->assertSame($expectedId, EducationVideoUrl::youtubeVideoId($url));
	}

	public static function youtubeUrlProvider(): array
	{
		return [
			'watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
			'short url' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
			'embed url' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
			'non youtube' => ['https://example.com/video.mp4', null],
			'empty' => ['', null],
		];
	}

	public function test_normalizes_blank_to_null(): void
	{
		$this->assertNull(EducationVideoUrl::normalize('   '));
	}
}
