<?php

class ZcLanguage {
	private $loadFiles = array();

	private $currentLanguage;
	private $defaultLanguage;

	private $defaultLanguageData = array();
	private $currentLanguageData = array();

	private $baseDir;

	public function __construct($currentLanguage, $defaultLanguage) {
		$this->currentLanguage = $currentLanguage;
		$this->defaultLanguage = $defaultLanguage;
		$this->baseDir = ZcFactory::getConfig()->get(ZcConfigConst::DirFsApp) . 'languages/';
	}

	public function get($key) {
		$key = trim($key);
		if (isset($this->currentLanguageData[$key])) {
			return $this->currentLanguageData[$key];
		} elseif (isset($this->defaultLanguageData[$key])) {
			return $this->defaultLanguageData[$key];
		} else {
			return $key;
		}
	}

	public function loadByFile($langFile, $language = '') {
		if (!file_exists($langFile)) {
			return false;
		} else {
			if (array_search($langFile, $this->loadFiles) !== false) {
				//dump("试图重复加载 $langFile");
				return false;
			}
		}

		//选择是要合并到哪个
		$mergeTo = 'default';
		if (empty($language)) {
			$mergeTo = (strpos($langFile, $this->baseDir . $this->currentLanguage) === 0) ? 'current' : 'default';
		} else {
			$mergeTo = $this->currentLanguage == $language ? 'current' : 'default';
		}

		$langArray = include($langFile);
		if ($mergeTo == 'current') {
			$this->currentLanguageData = array_merge($this->currentLanguageData, $langArray);
		} else {
			$this->defaultLanguageData = array_merge($this->defaultLanguageData, $langArray);
		}
		$this->loadFiles[] = $langFile;
	}

	private function getModuleLanguageFile($route, $module, $language) {
		$langFiles = array();

		$langFiles[] = $this->baseDir . $language . '/common.php';

		$path = $this->baseDir . $language . '/' . $module . '/';
		$langFiles[] = $path . 'common.php';

		$parts = explode('/', $route);
		$lastPart = array_pop($parts);
		foreach ($parts as $part) {
			$path .= $part . '/';

			$langFiles[] = $path . 'common.php';
		}
		$langFiles[] = $path . $lastPart . '.php';

		return $langFiles;
	}

	public function loadControllerLanguageByRoute($route) {
		$langFiles = $this->getModuleLanguageFile($route, 'controller', $this->currentLanguage);

		foreach ($langFiles as $langFile) {
			$this->loadByFile($langFile, $this->currentLanguage);
		}

		if ($this->currentLanguage != $this->defaultLanguage) {
			$langFiles = $this->getModuleLanguageFile($route, 'controller', $this->defaultLanguage);
			foreach ($langFiles as $langFile) {
				$this->loadByFile($langFile, $this->defaultLanguage);
			}
		}
	}

	/**
	 * 加载widget语言文件
	 * @param unknown_type $route
	 */
	public function loadWidgetLanguageByRoute($route) {
		$langFiles = $this->getModuleLanguageFile($route, 'widget', $this->currentLanguage);
		foreach ($langFiles as $langFile) {
			$this->loadByFile($langFile, $this->currentLanguage);
		}

		if ($this->currentLanguage != $this->defaultLanguage) {
			$langFiles = $this->getModuleLanguageFile($route, 'widget', $this->defaultLanguage);
			foreach ($langFiles as $langFile) {
				$this->loadByFile($langFile, $this->defaultLanguage);
			}
		}
	}

	/**
	 * 加载一个语言文件
	 * @param unknown_type $lang
	 */
	public function loadLangugeFile($lang, $dir = '') {
		$lang = strpos($lang, '.php') === false? $lang . '.php' : $lang;
		$langFile = realpath($this->baseDir . $this->currentLanguage . '/' . $dir . '/' . trim($lang));
		$this->loadByFile($langFile);
	}
}