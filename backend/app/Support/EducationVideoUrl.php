<?php

namespace App\Support;

class EducationVideoUrl
{
	public static function normalize(?string $url): ?string
	{
		$url = trim((string) $url);

		return $url === '' ? null : $url;
	}

	public static function youtubeVideoId(?string $url): ?string
	{
		$url = self::normalize($url);
		if ($url === null) {
			return null;
		}

		$parts = parse_url($url);
		if (! is_array($parts)) {
			return null;
		}

		$host = strtolower((string) ($parts['host'] ?? ''));
		$path = (string) ($parts['path'] ?? '');

		if (str_contains($host, 'youtu.be')) {
			$id = ltrim($path, '/');

			return self::isValidYoutubeId($id) ? $id : null;
		}

		if (str_contains($host, 'youtube.com') || str_contains($host, 'youtube-nocookie.com')) {
			parse_str((string) ($parts['query'] ?? ''), $query);
			if (isset($query['v']) && self::isValidYoutubeId((string) $query['v'])) {
				return (string) $query['v'];
			}

			if (preg_match('#/(embed|shorts|live)/([A-Za-z0-9_-]{11})#', $path, $matches)) {
				return $matches[2];
			}
		}

		return null;
	}

	public static function youtubeEmbedUrl(?string $url): ?string
	{
		$id = self::youtubeVideoId($url);

		return $id !== null ? "https://www.youtube.com/embed/{$id}" : null;
	}

	private static function isValidYoutubeId(string $id): bool
	{
		return (bool) preg_match('/^[A-Za-z0-9_-]{11}$/', $id);
	}
}
