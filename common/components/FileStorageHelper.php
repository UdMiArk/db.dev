<?php


namespace common\components;


use common\models\Market;
use common\models\Product;
use common\models\Resource;
use yii\base\Exception;
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

	public static function archiveResourceBucket(Resource $resource) {
		throw new \Exception("Not Implemented");
	}

	public static function unpackProductBucket(Resource $resource) {
		throw new \Exception("Not Implemented");
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

	// TODO: remake it into background task
	// Redis + yii2-queue

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
}