<?php

if (!function_exists('compress_png')) {
	/**
	 * Optimizes PNG file with pngquant 1.8 or later (reduces file size of 24-bit/32-bit PNG images).
	 *
	 * You need to install pngquant 1.8 on the server (ancient version 1.0 won't work).
	 * There's package for Debian/Ubuntu and RPM for other distributions on http://pngquant.org
	 *
	 * @param $pathToPngFile string
	 * @param $maxQuality int
	 * @throws Exception
	 * @return boolean
	 */
	function compress_png(string $pathToPngFile, int $maxQuality = 90)
	{
		if (!file_exists($pathToPngFile)) {
			throw new Exception('File does not exist: ' . $pathToPngFile);
		}

		$minQuality = 60;

		/*
		 * --== Workaround for MAC start ==--
		 */
		$validPngquantLocations = [
			'/usr/bin/pngquant',
			'/usr/local/bin/pngquant'
		];

		$pngquant = '/usr/bin/pngquant';

		foreach ($validPngquantLocations as $validPngquantLocation) {
			if (file_exists($validPngquantLocation)) {
				$pngquant = $validPngquantLocation;
				break;
			}
		}
		/*
		 * --== Workaround for MAC end ==--
		 */

		$compressedPngContent = shell_exec("$pngquant --quality=$minQuality-$maxQuality - < " . escapeshellarg($pathToPngFile));

		if (!$compressedPngContent) {
			throw new Exception('Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?');
		}

		return file_put_contents($pathToPngFile, $compressedPngContent);
	}
}
