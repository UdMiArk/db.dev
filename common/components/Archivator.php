<?php


namespace common\components;


use PharData;
use yii\base\Exception;
use ZipArchive;

class Archivator {
	/**
	 * @param $srcDir
	 * @param $dstFileName
	 * @param array $additionalFiles
	 */

	public static function pack($srcDir, $dstFileName, $additionalFiles = null) {
		static::zipPack($srcDir, $dstFileName, $additionalFiles);
	}

	public static function zipPack($srcDir, $dstFileName, $additionalFiles = null) {
		$zip = new ZipArchive();
		if (!$zip->open($dstFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::EXCL)) {
			throw new Exception("Unable to open zip file");
		}
		try {
			if (!is_dir($srcDir) || !($handle = opendir($srcDir))) {
				throw new Exception("Unable to open source dir");
			}
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}
				$path = $srcDir . DIRECTORY_SEPARATOR . $file;
				if (!$zip->addFile($path, $file)) {
					throw new Exception("Failed to add file to archive: " . $file);
				}
			}
			closedir($handle);
			if ($additionalFiles) foreach ($additionalFiles as $fileName => $fileContent) {
				if (!$zip->addFromString($fileName, $fileContent)) {
					throw new Exception("Failed to add custom file to archive: " . $fileName);
				}
			}
		} finally {
			$zip->close();
		}
	}

	public static function unpack($srcFileName, $dstDir) {
		static::zipUnpack($srcFileName, $dstDir);
	}

	public static function zipUnpack($srcFileName, $dstDir) {
		$zip = new ZipArchive();
		if (!$zip->open($srcFileName)) {
			throw new Exception("Unable to open zip file");
		}
		try {
			$zip->extractTo($dstDir);
		} finally {
			$zip->close();
		}
	}

	public static function pharPack($srcDir, $dstFileName, $additionalFiles = null) {
		$phar = new PharData($dstFileName);
		$phar->buildFromDirectory($srcDir);
		if ($additionalFiles) {
			foreach ($additionalFiles as $fileName => $fileContent) {
				$phar->addFromString($fileName, $fileContent);
			}
		}
	}

	public static function pharUnpack($srcFileName, $dstDir) {
		$phar = new PharData($srcFileName);
		$phar->extractTo($dstDir);
	}
}