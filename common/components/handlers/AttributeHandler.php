<?php


namespace common\components\handlers;


use common\models\Resource;
use common\models\ResourceTypeAttribute;
use yii\base\Exception;

abstract class AttributeHandler {
	private $_attribute;
	private $_options;

	public function __construct(ResourceTypeAttribute $attribute, array $options) {
		$this->_attribute = $attribute;
		$this->_options = $options;
	}

	public function getAttribute() {
		return $this->_attribute;
	}

	public function getOptions() {
		return $this->_options;
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

	/**
	 * @param array $options
	 * @param array $errors
	 * @return array|null|false
	 */
	abstract public function sanitizeOptions($options, &$errors = []);
}