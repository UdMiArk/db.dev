<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\DateTimeStampBehavior;
use common\components\FileStorageHelper;
use common\data\EArchiveStatus;
use common\data\EResourceStatus;
use common\data\RBACData;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Resource model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $archived_by
 * @property integer $status_by
 * @property integer $product_id
 * @property integer $type_id
 *
 * @property string $created_at
 * @property string $updated_at
 * @property string $status_at
 * @property string $archived_at
 *
 * @property string $archived_queue
 *
 * @property array $data
 * @property string $data_json
 *
 * @property string $name
 * @property integer $status
 * @property integer $archived
 * @property string $path
 * @property string $comment
 *
 * @property-read User $user
 * @property-read User $archivedBy
 * @property-read User $statusBy
 * @property-read Product $product
 * @property-read ResourceType $type
 */
class Resource extends CommonRecord {
	const RBAC_ALL = 'app.resource.*';
	const RBAC_CREATE = 'app.resource.create';
	const RBAC_VIEW = 'app.resource.view';
	const RBAC_UPDATE = 'app.resource.update';
	const RBAC_DELETE = 'app.resource.delete';
	const RBAC_APPROVE = 'app.resource.approve';
	const RBAC_AUTO_APPROVE = 'app.resource.approve_own';
	const RBAC_ARCHIVE = 'app.resource.archive';

	const RBAC_ROLE_MODERATOR = 'app.resource.moderator';

	public function init() {
		parent::init();
		$this->on($this::EVENT_AFTER_INSERT, [$this, 'evHandleInsert']);
		$this->on($this::EVENT_AFTER_UPDATE, [$this, 'evHandleUpdate']);

		$this->on($this::EVENT_BEFORE_INSERT, [$this, 'evHandleBeforeInsert']);
		$this->on($this::EVENT_BEFORE_UPDATE, [$this, 'evHandleBeforeUpdate']);

		$this->on($this::EVENT_AFTER_DELETE, [$this, 'evHandleDelete']);

		$this->status = EResourceStatus::AWAITING;
	}

	public function generatePath() {
		$result = $this->name;
		if ($this->primaryKey) {
			$result .= ' (id ' . $this->primaryKey . ')';
		}
		return '/' . FileStorageHelper::preparePath($result);
	}

	protected function evHandleInsert() {
		$newPath = $this->generatePath();
		if ($this->path !== $newPath) {
			$this->path = $newPath;
			$this->setOldAttribute('path', $newPath);
			$this::updateAll(['path' => $newPath], ['id' => $this->primaryKey]);
		}
	}

	protected function evHandleUpdate() {
		$archived = $this->archived;
		if (!in_array($archived, [EArchiveStatus::AWAITING_DEARCHIVATION, EArchiveStatus::ARCHIVED])) {
			FileStorageHelper::updateResourceMetaFile($this);
		}
	}

	protected function evHandleDelete() {
		if ($this->isInArchivationProcess()) {
			throw new Exception("Ресурс в процессе архивации и не может быть удален");
		}
		FileStorageHelper::destroyResourceStorage($this);
	}

	protected function evHandleBeforeInsert() {
		$this->_checkStatusChange();
	}

	protected function evHandleBeforeUpdate() {
		$this->_checkStatusChange();
	}

	protected function _checkStatusChange() {
		if ($this->isAttributeChanged('status')) {
			if ($this->status !== EResourceStatus::AWAITING) {
				$this->status_at = date('Y-m-d H:i:s', time());
			} else {
				$this->status_at = null;
			}
		}
		if ($this->isAttributeChanged('archived')) {
			switch (intval($this->archived)) {
				case EArchiveStatus::NOT_ARCHIVED:
					$this->archived_at = null;
					break;
				case EArchiveStatus::ARCHIVED:
					$this->archived_at = date('Y-m-d H:i:s', time());
					break;
			}
		}
	}

	public function behaviors() {
		return [
			DateTimeStampBehavior::class,
		];
	}

	public function attributeLabels() {
		return [
			'user_id' => "Создатель",
			'product_id' => "Объект продвижения",
			'type_id' => "Тип ресурса",

			'created_at' => "Дата создания",
			'updated_at' => "Дата последнего изменения",
			'status_at' => "Дата утверждения",
			'archived_at' => "Дата архивации",

			'data' => "Данные",
			'name' => "Имя",
			'status' => "Статус",
			'archived' => "В архиве",
			'path' => "Путь",
			'comment' => "Комментарий",
		];
	}

	public function rules() {
		return array_merge(parent::rules(), [
			[['name', 'comment'], 'trim', 'on' => [$this::SCENARIO_CREATE]],
			[['name', 'comment'], 'string', 'on' => [$this::SCENARIO_CREATE]],
			[['type_id', 'product_id'], 'integer', 'on' => [$this::SCENARIO_CREATE]],
			[['name', 'type_id', 'product_id'], 'required', 'on' => [$this::SCENARIO_CREATE]],
			[['data'], 'safe', 'on' => [$this::SCENARIO_CREATE]],
		]);
	}

	public function getData() {
		$data = $this->data_json;
		return $data ? Json::decode($data) : [];
	}

	public function setData($val) {
		$this->data_json = empty($val) ? null : Json::encode($val);
	}

	public function getUser() {
		return $this->hasOne(User::class, ['id' => 'user_id']);
	}

	public function getArchivedBy() {
		return $this->hasOne(User::class, ['id' => 'archived_by']);
	}

	public function getStatusBy() {
		return $this->hasOne(User::class, ['id' => 'status_by']);
	}

	public function getProduct() {
		return $this->hasOne(Product::class, ['id' => 'product_id']);
	}

	public function getType() {
		return $this->hasOne(ResourceType::class, ['id' => 'type_id']);
	}

	public function getFrontendInfo($withUser = false, $withProduct = false, $withType = false) {
		$result = array_merge(parent::getFrontendInfo(), [
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
			'status_at' => $this->updated_at,
			'status_by' => $this->status_by,
			'archived_at' => $this->archived_at,
			'archived_by' => $this->archived_by,
			'archived_queue' => $this->archived_queue,
			'name' => $this->name,
			'status' => $this->status,
			'archived' => $this->archived,
			'comment' => $this->comment,
			'data' => $this->data,
		]);

		if ($withUser) {
			$result['user'] = $this->user->getFrontendInfo();
			$statusChanger = $this->statusBy;
			$result['statusBy'] = $statusChanger ? $statusChanger->getFrontendInfo() : null;
		}
		if ($withProduct) {
			$result['product'] = $this->product->getFrontendInfo(true);
		}
		if ($withType) {
			$result['type'] = $this->type->getFrontendInfo(true);
		}

		return $result;
	}

	public function isInArchivationProcess() {
		return !in_array($this->archived, [EArchiveStatus::ARCHIVED, EArchiveStatus::NOT_ARCHIVED]);
	}

	public function getRightsData() {
		$result = [];

		$user = \Yii::$app->user;

		if ($this->status === EResourceStatus::AWAITING) {
			if ($user->can($this::RBAC_APPROVE, ['target' => $this])) {
				$result['canApprove'] = true;
				$result['canDelete'] = true;
			}
			if ($user->id === $this->user_id) {
				$result['canDelete'] = true;
			}
		} else {
			$noArchivingInProcess = !$this->isInArchivationProcess();
			if (
				$noArchivingInProcess
				&& $user->can($this::RBAC_ARCHIVE, ['target' => $this])
			) {
				$result['canArchive'] = true;
			}
			if ($this->status === EResourceStatus::REJECTED) {
				if (
					$noArchivingInProcess
					&&
					($user->id === $this->user_id || $user->can($this::RBAC_APPROVE, ['target' => $this]))
				) {
					$result['canDelete'] = true;
				}
			} else {
				if ($user->can(RBACData::ROLE_SUPERUSER)) {
					$result['canDelete'] = true;
				}
			}
		}

		return $result;
	}

}
