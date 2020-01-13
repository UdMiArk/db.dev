<?php


namespace backend\components;

use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\Json;
use yii\web\Request;

class ListRequestProcessor {
	public $paramPage = 'page';
	public $paramPageSize = 'pageSize';

	public $defaultPageSize = 20;

	public $paramFilters = 'filters';

	public $filterHandlers = null;
	public $filterOnlyByHandlers = false;
	public $defaultFilters = null;

	public $sortingHandlers = null;
	public $sortingOnlyByHandlers = false;

	public $paramSorting = 'sorting';
	public $paramSortingDesc = 'desc';
	public $paramSortingAsc = 'asc';

	/* @var Request */
	public $request = null;

	/**
	 * ListRequestProcessor constructor.
	 * @param Request $request
	 */
	public function __construct($request = null) {
		$this->request = is_null($request) ? \Yii::$app->request : $request;
	}

	/**
	 * @param ActiveQuery $query
	 * @return ActiveQuery
	 */
	public function apply($query) {

		$this->applyPagination($query);
		$this->applyFilters($query);
		$this->applySorting($query);

		return $query;
	}

	/**
	 * @param ActiveQuery $query
	 * @return ActiveQuery
	 */
	public function applyPagination($query) {
		$pageSize = $this->getPageSize();
		$page = $this->getPage();
		if ($pageSize) {
			$query->limit($pageSize);

			if ($page) {
				$query->offset($page * $pageSize);
			}
		}

		return $query;
	}

	/**
	 * @return int
	 */
	public function getPageSize() {
		return intval($this->request->get($this->paramPageSize, $this->defaultPageSize));
	}

	/**
	 * @return int
	 */
	public function getPage() {
		return intval($this->request->get($this->paramPage));
	}

	/**
	 * @param ActiveQuery $query
	 * @return ActiveQuery
	 */
	public function applyFilters($query) {
		$filters = $this->getFilters();
		if ($this->filterHandlers) foreach ($this->filterHandlers as $filter => $handler) {
			if (!is_null(@$filters[$filter])) {
				call_user_func($handler, $query, $filters[$filter], $filter, $this);
				unset($filters[$filter]);
			}
		}
		if (!$this->filterOnlyByHandlers && !empty($filters)) {
			$query->andWhere($filters);
		}
		return $query;
	}

	public function getFilters() {
		$filters = \Yii::$app->request->get('filters');
		$filters = empty($filters) ? [] : (is_string($filters) ? Json::decode($filters) : $filters);
		if ($this->defaultFilters) {
			$filters = array_merge($this->defaultFilters, $filters);
		}
		return $filters;
	}

	/**
	 * @param ActiveQuery $query
	 * @return ActiveQuery
	 */
	public function applySorting($query) {
		$sorting = $this->getSorting();
		if (!empty($sorting)) {
			if ($this->sortingHandlers && count($sorting) === 1 && array_key_exists(array_keys($sorting)[0], $this->sortingHandlers)) {
				$attr = array_keys($sorting)[0];
				$dir = $sorting[$attr];
				call_user_func($this->sortingHandlers[$attr], $query, $dir, $attr, $this);
			} elseif (!$this->sortingOnlyByHandlers) {
				$query->orderBy($sorting);
			}
		}
		return $query;
	}

	/**
	 * @return array
	 */
	public function getSorting() {
		$sortingStr = $this->request->get($this->paramSorting);
		if (empty($sortingStr)) {
			return null;
		}
		$sorting = [];
		foreach (explode('|', $sortingStr) as $sortingItem) {
			$sortingItem = explode(':', $sortingItem);
			$attr = $sortingItem[0];
			$dir = @$sortingItem[1] === $this->paramSortingDesc ? SORT_DESC : SORT_ASC;
			if (!empty($attr)) {
				$sorting[$attr] = $dir;
			}
		}

		return $sorting;
	}

	/**
	 * @param ActiveQuery $query
	 * @param array $columns
	 * @param array $totals
	 * @return array[]
	 * @throws \Exception
	 */
	public function prepare($query, $columns = null, $totals = null) {
		$count = intval($query->count());
		$result = [];
		$idx = 0;
		if ($count > 0) foreach ($query->each() as $row) {
			$result [] = $this->prepareRow($row, $idx, $columns);
			$idx++;
		}

		$result = [
			'count' => $count,
			'items' => $result,
		];

		if ($totals) {
			$result['totals'] = [];
			foreach ($totals as $key => $expr) {
				$value = null;
				if (is_string($expr)) {
					switch ($expr) {
						case 'sum':
							$value = $query->sum('`' . $key . '`');
							if (!is_null($value)) {
								$value = floatval($value);
							}
							break;
						default:
							throw new \Exception('Неизвестный метод подсчета: ' . $expr);
					}
				} else {
					$value = call_user_func($expr, $query);
				}
				$result['totals'][$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * @param Model $row
	 * @param integer $idx
	 * @param array $columns
	 * @return array
	 */
	public function prepareRow($row, $idx, $columns = null) {
		$result = [];
		if (is_null($columns)) {
			$result = $row->getAttributes();
		} else foreach ($columns as $name => $processor) {
			if (is_string($processor)) {
				if (is_int($name)) {
					$name = $processor;
				}
				$value = $row->{$processor};
			} else {
				$value = call_user_func($processor, $row, $idx, $this);
			}
			$result[$name] = $value;
		}
		return $result;
	}
}