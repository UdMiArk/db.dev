<?php


namespace common\components\handlers;


use common\models\Resource;
use common\models\ResourceTypeAttribute;
use yii\base\Exception;

abstract class AttributeHandler {
	private $_attribute;

	public function __construct(ResourceTypeAttribute $attribute) {
		$this->_attribute = $attribute;
	}

	public function getAttribute() {
		return $this->_attribute;
	}

	/**
	 * @param mixed $data
	 * @param bool $unprocessed
	 * @return string[]
	 */
	abstract public function getFileNames($data, $unprocessed = false);

	/**
	 * @param mixed $data
	 * @param bool $unprocessed
	 * @param array $errors
	 * @return boolean
	 */
	abstract public function validate($data, $unprocessed = false, &$errors = []);

	/**
	 * @param $data
	 * @param Resource $resource
	 * @return mixed
	 * @throws Exception
	 */
	abstract public function process($data, Resource $resource);
}