<?php


namespace common\components;


use common\models\Market;
use common\models\Product;
use common\models\Resource;
use PharData;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii2tech\filestorage\local\Bucket;
use yii2tech\filestorage\StorageInterface;

class FileStorageHelper {
	const META_FILE_NAME = '__meta__.txt';

	public static function preparePath($name) {
		// FIXME: Ensure there's no forbidden symbols
		$name = str_replace(['/', '\\', '|', '<', '>', ':', '*', '?', '"', '%'], "_", $name);
		if ($name === '.' || $name === '..') {
			throw new Exception("Forbidden file name: '" . $name . "'");
		}
		return $name;
	}

	public static function getMarketBucket(Market $market) {
		$fileStorage = static::getFileStorage();
		$bucketName = static::getMarketBucketName($market);

		if (!$fileStorage->hasBucket($bucketName)) {
			$fileStorage->addBucket($bucketName, static::createMarketBucket($market));
		}
		return $fileStorage->getBucket($bucketName);
	}

	/**
	 * @return StorageInterface
	 */
	public static function getFileStorage() {
		return \Yii::$app->fileStorage;
	}

	public static function getMarketBucketName(Market $market) {
		return $market->path;
	}

	protected static function createMarketBucket(Market $market) {
		// TODO: make it work with different types of buckets
		$bucket = new Bucket();
		$bucket->setStorage(static::getFileStorage());
		$bucket->baseSubPath = trim($market->path, '/');

		return $bucket;
	}

	public static function getProductBucket(Product $product) {
		$fileStorage = static::getFileStorage();
		$bucketName = static::getProductBucketName($product);

		if (!$fileStorage->hasBucket($bucketName)) {
			$fileStorage->addBucket($bucketName, static::createProductBucket($product));
		}
		return $fileStorage->getBucket($bucketName);
	}

	public static function getProductBucketName(Product $product) {
		return $product->market->path . $product->path;
	}

	protected static function createProductBucket(Product $product) {
		// TODO: make it work with different types of buckets
		$bucket = new Bucket();
		$bucket->setStorage(static::getFileStorage());
		$bucket->baseSubPath = trim($product->market->path . $product->path, '/');

		return $bucket;
	}

	public static function updateResourceMetaFile(Resource $resource) {
		// TODO: Forbid change if in archive
		$bucket = static::getResourceBucket($resource);
		if (!$bucket->exists()) {
			throw new Exception("Не найдено хранилеще файлов ресурса");
		}
		if (!$bucket->saveFileContent(
			static::META_FILE_NAME,
			static::generateResourceMetaDescription($resource)
		)) {
			throw new Exception("Не удалось сохранить мета-файл ресурса " . $resource->primaryKey);
		}
	}

	public static function getResourceBucket(Resource $resource) {
		$fileStorage = static::getFileStorage();
		$bucketName = static::getResourceBucketName($resource);

		if (!$fileStorage->hasBucket($bucketName)) {
			$fileStorage->addBucket($bucketName, static::createResourceBucket($resource));
		}
		return $fileStorage->getBucket($bucketName);
	}

	public static function getResourceBucketName(Resource $resource) {
		$product = $resource->product;
		return $product->market->path . $product->path . $resource->path;
	}

	protected static function createResourceBucket(Resource $resource) {
		// TODO: make it work with different types of buckets
		$bucket = new Bucket();
		$bucket->setStorage(static::getFileStorage());
		$product = $resource->product;
		$bucket->baseSubPath = trim($product->market->path . $product->path . $resource->path, '/');

		return $bucket;
	}

	public static function generateResourceMetaDescription(Resource $resource) {
		return \Yii::$app->view->render('@common/views/resource/meta.php', ['resource' => $resource]);
	}

	public static function getResourceArchiveName(Resource $resource) {
		return static::preparePath(ltrim($resource->path, '\\/')) . '.zip';
		//return static::preparePath($resource->name).'.zip';
	}


	public static function archiveResourceBucket(Resource $resource, callable $successHandler = null) {
		$resourceBucket = static::getResourceBucket($resource);
		if (!$resourceBucket->exists()) {
			throw new Exception("Нет доступа к хранилищу файлов ресурса '" . $resource->primaryKey . "'");
		}
		$productBucket = static::getProductBucket($resource->product);
		if (!$productBucket->exists()) {
			if (!$productBucket->create()) {
				throw new Exception("Нет доступа к хранилищу ресурсов объекта продвижения '" . $resource->product_id . "'");
			}
		}
		// TODO: Support other storage options
		if ($resourceBucket instanceof Bucket) {
			$resourceDir = $resourceBucket->getFullBasePath();
			$simpleTempFileName = tempnam(sys_get_temp_dir(), 'ARCHIVE');
			$tempFileName = $simpleTempFileName . '.zip';
			try {
				$phar = new PharData($tempFileName);
				$phar->buildFromDirectory($resourceDir);
				$phar->addFromString(static::META_FILE_NAME, static::generateResourceMetaDescription($resource));
				$archivedResourceName = static::getResourceArchiveName($resource);
				if (!$productBucket->copyFileIn($tempFileName, $archivedResourceName)) {
					throw new Exception("Не удалось записать архивную версию ресурса '" . $resource->name . "' в хранилище ОП");
				}
			} finally {
				unset($phar);
				isset($tempFileName) && unlink($tempFileName);
				isset($simpleTempFileName) && unlink($simpleTempFileName);
			}
			if ($successHandler) {
				$trans = \Yii::$app->db->beginTransaction();
				try {
					// final check that resources folder can be deleted
					// Other option is just to ignore if unable to delete
					$resourceTestDir = $resourceDir . '_archived';
					if (!@rename($resourceDir, $resourceTestDir)) {
						throw new Exception("Не удалось удалить хранилище файлов ресурса");
					}
					if (!@rename($resourceTestDir, $resourceDir)) {
						throw new Exception("Не удалось удалить хранилище файлов ресурса");
					}
					$successHandler();
					$resourceBucket->destroy();
					$trans->commit();
				} catch (\Throwable $e) {
					$trans->rollBack();
					$productBucket->deleteFile($archivedResourceName);
					throw $e;
				}
			}
		} else {
			throw new Exception("Используемое хранилище ресурса не поддерживает архивацию");
		}
	}

	public static function unpackResourceBucket(Resource $resource, callable $successHandler = null) {
		$resourceBucket = static::getResourceBucket($resource);
		if ($resourceBucket->exists()) {
			throw new Exception("Хранилище файлов ресурса '" . $resource->primaryKey . "' уже занято?");
		}
		$productBucket = static::getProductBucket($resource->product);
		$archivedResourceName = static::getResourceArchiveName($resource);
		if (!$productBucket->fileExists($archivedResourceName)) {
			throw new Exception("Нет доступа к архиву хранилища ресурса '" . $resource->primaryKey . "'");
		}
		// TODO: Support other storage options
		if ($resourceBucket instanceof Bucket) {
			$resourceDir = $resourceBucket->getFullBasePath();
			$simpleTempFileName = tempnam(sys_get_temp_dir(), 'ARCHIVE');
			$tempFileName = $simpleTempFileName . '.zip';
			try {
				if (!$productBucket->copyFileOut($archivedResourceName, $tempFileName)) {
					throw new Exception("Не удалось прочитать архив хранилища ресурса '" . $resource->primaryKey . "'");
				}
				$phar = new PharData($tempFileName);
				$phar->extractTo($resourceDir);
			} finally {
				unset($phar);
				isset($tempFileName) && unlink($tempFileName);
				isset($simpleTempFileName) && unlink($simpleTempFileName);
			}
			if ($successHandler) {
				$trans = \Yii::$app->db->beginTransaction();
				try {
					$successHandler();
					$productBucket->deleteFile($archivedResourceName);
					$trans->commit();
				} catch (\Throwable $e) {
					$trans->rollBack();
					FileHelper::removeDirectory($resourceDir);
					throw $e;
				}
			}
		} else {
			throw new Exception("Используемое хранилище ресурса не поддерживает архивацию");
		}
	}

	public static function destroyResourceStorage(Resource $resource) {
		if ($resource->archived) {
			$bucket = FileStorageHelper::getProductBucket($resource->product);
			if (!$bucket->deleteFile(FileStorageHelper::getResourceArchiveName($resource))) {
				throw new Exception("Не удалось удалить архивированное хранилище ресурса");
			}
		} else {
			$bucket = FileStorageHelper::getResourceBucket($resource);
			if (!$bucket->destroy()) {
				throw new Exception("Не удалось удалить хранилище ресурса");
			}
		}
	}

}