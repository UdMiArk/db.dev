<?php


namespace backend\models;

use common\components\FileStorageHelper;
use common\components\FormModel;
use common\components\ProductAccessibilityHelper;
use common\data\EResourceStatus;
use common\models\Market;
use common\models\Product;
use common\models\Resource;
use common\models\ResourceType;
use common\models\User;

/**
 * Class FormResourceCreate
 * @package backend\models
 */
class FormResourceCreate extends FormModel {
	public $product_id_ext;
	public $type_id;
	public $name;
	public $comment;
	public $data;

	/* @var Resource */
	protected $_model;
	/* @var ResourceType */
	protected $_type;
	/* @var Product */
	protected $_product;
	/* @var User */
	protected $_user;

	public function init() {
		parent::init();
		$this->_model = new Resource();
		$this->_user = \Yii::$app->user->identity;
	}

	public function attributeLabels() {
		return array_merge($this->_model->attributeLabels(), [
			'product_id_ext' => "Объект продвижения",
		]);
	}

	public function rules() {
		return [
			[['name', 'comment'], 'trim'],
			[['name', 'comment'], 'string'],
			[['product_id_ext', 'type_id', 'name'], 'required'],

			[['type_id'], 'ruleTypeExists'],
			[['data'], 'ruleDataValidatedAgainstType', 'skipOnEmpty' => false],
			[['product_id_ext'], 'ruleProductAvailable'],
		];
	}

	public function ruleTypeExists() {
		if ($this->type_id) {
			$this->_type = ResourceType::findOne(['id' => $this->type_id]);
			if (!$this->_type) {
				$this->addError('type_id', "Не удалось определить тип ресурса: " . $this->type_id);
			}
		}
	}

	public function ruleDataValidatedAgainstType($attr) {
		if ($this->_type) {
			if (!$this->_type->validateResourceData($this->{$attr}, true, $errors)) {
				if ($errors) {
					foreach ($errors as $key => $error) {
						$errorKey = $attr . '.' . $key;
						if (is_array($error)) foreach ($error as $_er) {
							$this->addError($errorKey, $_er);
						} else {
							$this->addError($errorKey, $error);
						}
					}
				} else {
					$this->addError($attr, "Не удалось проверить данные формы для типа " . $this->_type->primaryKey);
				}
			}
		}
	}

	public function ruleProductAvailable($attr) {
		if (strlen($this->{$attr})) {
			if (!$this->_user) {
				$this->addError($attr, "Не удалось определить принадлежность ресурса");
			} else {
				$availableProducts = ProductAccessibilityHelper::getAvailableProducts($this->_user, $errors);
				if (!$availableProducts) {
					if ($errors) foreach ($errors as $error) {
						$this->addError($attr, $error);
					} else {
						$this->addError($attr, "Отстутствуют доступные для управления объекты продвижения");
					}
				} else {
					$foundProductData = null;
					$foundProductKey = $this->{$attr};
					foreach ($availableProducts as $productData) {
						if ($productData['id'] === $foundProductKey) {
							$foundProductData = $productData;
							break;
						}
					}
					if (!$foundProductData) {
						$this->addError($attr, "Не удалось найти объект продвижения среди доступных");
					} else {
						$marketId = Market::find()->andWhere(['id_ext' => $foundProductData['market']])->select(['id'])->scalar();
						if ($marketId === false) {
							$this->addError($attr, "Выбранный объект продвижения принадлежит неизвестному рынку: " . $foundProductData['market']);
						} else {
							$this->_product = Product::findOne(['id_ext' => $foundProductData['id']]);
							if (!$this->_product) {
								$this->_product = new Product();
								$this->_product->id_ext = $foundProductData['id'];
								$this->_product->path = $this->_product->generatePath();
								$this->_product->market_id = intval($marketId);
							} else if (strval($this->_product->market_id) !== strval($marketId)) {
								$this->addError($attr, "Реальный рынок продукта не соответствует хранимому в базе. Пожалуйста сообщите администратору");
							}
							$this->_product->name = $foundProductData['name'];
						}
					}
				}
			}
		}
	}

	public function save() {
		if (!$this->validate()) {
			return null;
		}

		$resourceBucket = null;
		$trans = \Yii::$app->db->beginTransaction();
		try {
			if (!empty($this->_product->getDirtyAttributes())) {
				if (!$this->_product->save()) {
					$this->addError('product_id_ext', $this->_product->getFirstError());
					$trans->rollBack();
					return null;
				}
			}

			$resource = new Resource();
			// TODO: Make model behavior to set model relations wiht models (ie "->user =" instead of "->user_id =")
			$resource->user_id = $this->_user->primaryKey;
			$resource->type_id = $this->_type->primaryKey;
			$resource->product_id = $this->_product->primaryKey;
			$resource->name = $this->name;
			$resource->comment = $this->comment;
			$resource->path = $resource->generatePath();
			if (\Yii::$app->authManager->checkAccess($resource->user_id, $resource::RBAC_APPROVE)) {
				$resource->status = EResourceStatus::APPROVED;
				$resource->status_by = $resource->user_id;
			}

			if (!$resource->save()) {
				$this->addErrors($resource->getErrors());
				$trans->rollBack();
				return null;
			}
			// Resource bucket created after save because $resource->path may be affected by save
			// (for example id appended to end for uniqueness)
			$resourceBucket = FileStorageHelper::getResourceBucket($resource);
			if ($resourceBucket->exists()) {
				$this->addError('name', "Указанное имя уже занято другим ресурсом");
				$trans->rollBack();
				$resourceBucket = null;
				return null;
			}
			$resourceBucket->create();

			// TODO: ensure /tmp folder cleared up periodically
			$processedData = $this->_type->processResourceData($this->data, $resource);

			if ($processedData) {
				$resource->data = $processedData;
				if (!$resource->update()) {
					$this->addErrors($resource->getErrors());
					$resourceBucket->destroy();
					$trans->rollBack();
					return null;
				}
			} else {
				FileStorageHelper::updateResourceMetaFile($resource);
			}

			$trans->commit();
		} catch (\Exception $exception) {
			$trans->rollBack();
			if ($resourceBucket && $resourceBucket->exists()) {
				$resourceBucket->destroy();
			}
			throw $exception;
		}

		return $resource;
	}
}