<?php


namespace console\components;


use Yii;
use yii\console\Controller;

class ConsoleController extends Controller {
	private $_progressCountTotal = -1;
	private $_progressCountCurrent = 0;
	private $_progressCountLastStrLen = 0;
	private $_progressCountShowCurrent = false;

	public function stdout($string) {
		$this->_progressCountLastStrLen = 0;
		$result = parent::stdout($string);
		Yii::info($string, 'application.' . $this->route);
		return $result;
	}

	public function stdoutNl($string, $suffix = "\n", $prefix = '') {
		$this->_progressCountLastStrLen = 0;
		$result = parent::stdout($prefix . $string . $suffix);
		Yii::info($string, 'application.' . $this->route);
		return $result;
	}

	public function stdwrnNl($string, $suffix = "\n", $prefix = '') {
		$this->_progressCountLastStrLen = 0;
		$result = parent::stderr($prefix . $string . $suffix);
		Yii::warning($string, 'application.' . $this->route);
		return $result;
	}

	public function stderr($string) {
		$this->_progressCountLastStrLen = 0;
		$result = parent::stderr($string);
		Yii::error($string, 'application.' . $this->route);
		return $result;
	}

	public function stderrNl($string, $suffix = "\n", $prefix = '') {
		$this->_progressCountLastStrLen = 0;
		$result = parent::stderr($prefix . $string . $suffix);
		Yii::error($string, 'application.' . $this->route);
		return $result;
	}

	public function printProgress($totalCount, $showCurrent = true, $startingCount = 0) {
		$this->_progressCountTotal = $totalCount;
		$this->_progressCountCurrent = $startingCount;
		$this->_progressCountShowCurrent = $showCurrent;
		$this->_progressCountLastStrLen = 0;
		$this->printProgressAdvance(0);
	}

	public function printProgressAdvance($step = 1) {
		$this->_progressCountCurrent += $step;
		$i = $this->_progressCountCurrent;
		$percent = floor(($i * 100) / $this->_progressCountTotal);
		$lastStr = $percent . '%';
		$lastStr = $i . ' (' . $lastStr . ')';
		print str_repeat(chr(8), $this->_progressCountLastStrLen) . $lastStr;
		$this->_progressCountLastStrLen = strlen($lastStr);
	}
}