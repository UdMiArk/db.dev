<?php


namespace common\components;

use Adldap\Adldap;
use yii\base\Component;

/**
 * Class Adldap2Wrapper
 *
 * Almost the same as Edvlerblog\Adldap2\Adldap2Wrapper but with minor fixes for autocompletion
 *
 * @package common\components
 *
 * @mixin Adldap
 */
class Adldap2Wrapper extends Component {
	/**
	 * The Adldap instance.
	 */
	public $adLdapInstance;

	/**
	 * Array containig providers config
	 */
	public $providers;

	/*
	 * The name of the default provider
	 */
	public $defaultProvider = "default";

	/**
	 * init() called by yii.
	 */
	public function init() {
		if (!isset($this->adLdapInstance)) {
			$this->adLdapInstance = new Adldap();
		}

		foreach ($this->providers as $providerName => $prodivderSettings) {
			$config = new \Adldap\Connections\Provider($prodivderSettings['config']);
			$this->adLdapInstance->addProvider($config, $providerName);

			if (array_key_exists('schema', $prodivderSettings) && is_object($prodivderSettings['schema'])) {
				$this->adLdapInstance->getProvider($providerName)->setSchema($prodivderSettings['schema']);
			}

			if (@$prodivderSettings['autoconnect'] == true) {
				$this->adLdapInstance->connect($providerName);
			}
		}

		$providers = $this->adLdapInstance->getProviders();

		if (array_key_exists($this->defaultProvider, $providers)) {
			$this->adLdapInstance->setDefaultProvider($this->defaultProvider);
		} else {
			throw new \yii\base\Exception("The given defaultprovder with the name " . $this->defaultProvider . " could not be found. See https://github.com/edvler/yii2-adldap-module/blob/master/readme.md");
		}
	}


	/**
	 * Use magic PHP function __call to route ALL function calls to the Adldap class.
	 * Look into the Adldap class for possible functions.
	 *
	 * @param string $methodName Method name from Adldap class
	 * @param array $methodParams Parameters pass to method
	 * @return mixed
	 */
	public function __call($methodName, $methodParams) {
		return call_user_func_array([$this->adLdapInstance, $methodName], $methodParams);
	}
}