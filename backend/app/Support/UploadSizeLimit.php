<?php

namespace App\Support;

class UploadSizeLimit
{
	public const POSTER_IMAGE_MAX_KILOBYTES = 2048;

	public static function iniBytes(string $directive): int
	{
		return self::parseIniSize((string) ini_get($directive));
	}

	public static function parseIniSize(string $value): int
	{
		$value = trim($value);

		if ($value === '' || $value === '-1') {
			return PHP_INT_MAX;
		}

		if (! preg_match('/^(\d+(?:\.\d+)?)\s*([kmg])?$/i', $value, $matches)) {
			return (int) $value;
		}

		$number = (float) $matches[1];
		$unit = strtolower($matches[2] ?? '');

		return (int) match ($unit) {
			'g' => $number * 1024 * 1024 * 1024,
			'm' => $number * 1024 * 1024,
			'k' => $number * 1024,
			default => $number,
		};
	}

	public static function posterImageMaxBytes(): int
	{
		$validationBytes = self::POSTER_IMAGE_MAX_KILOBYTES * 1024;
		$uploadMaxBytes = self::iniBytes('upload_max_filesize');

		return min($validationBytes, $uploadMaxBytes);
	}

	public static function formatBytes(int $bytes): string
	{
		if ($bytes >= 1024 * 1024) {
			$megabytes = $bytes / (1024 * 1024);

			return rtrim(rtrim(number_format($megabytes, 1, ',', '.'), '0'), ',').' MB';
		}

		if ($bytes >= 1024) {
			$kilobytes = (int) round($bytes / 1024);

			return number_format($kilobytes, 0, ',', '.').' KB';
		}

		return number_format($bytes, 0, ',', '.').' byte';
	}

	public static function posterImageMaxLabel(): string
	{
		return self::formatBytes(self::posterImageMaxBytes());
	}

	public static function posterImageUploadHint(): string
	{
		$effectiveLabel = self::posterImageMaxLabel();
		$serverBytes = self::iniBytes('upload_max_filesize');
		$serverLabel = self::formatBytes($serverBytes);
		$appBytes = self::POSTER_IMAGE_MAX_KILOBYTES * 1024;

		if ($serverBytes <= $appBytes && $serverBytes < PHP_INT_MAX) {
			return "Maks. {$effectiveLabel} per file (batas unggahan server: {$serverLabel}).";
		}

		return "Maks. {$effectiveLabel} per file (batas aplikasi lebih ketat dari server {$serverLabel}).";
	}
}
