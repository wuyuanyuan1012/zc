<?php
/**
 * Zc框架的对象工厂
 *
 * @author tangjianhui 2013-6-28 下午3:16:06
 *
 */
class ZcFactory {
	//对象池
	private static $objectPool = array();

	static function singleton($className) {
		if (empty($className)) {
			trigger_error('empty class name');
			return false;
		}

		if (isset(self::$objectPool[$className])) {
			return self::$objectPool[$className];
		}

		if (class_exists($className)) {
			$obj = new $className;
			if ($obj instanceof ZcIInit) {
				$obj->init();
			}
			self::$objectPool[$className] = $obj;
			return self::$objectPool[$className];
		} else {
			return false;
		}
	}

	/**
	 * 全局配置项
	 * @return ZcConfig
	 */
	static function getConfig() {
		return self::singleton('ZcConfig');
	}

	/**
	 * 全局唯一语言对象
	 * @return ZcLanguage
	 */
	static function getLanguageObject() {
		if (empty(self::$objectPool['language'])) {
			self::$objectPool['language'] = new ZcLanguage(Zc::C(ZcConfigConst::LanguageCurrent), Zc::C(ZcConfigConst::LanguageDefault));
		}
		return self::$objectPool['language'];
	}

	/**
	 *
	 * @return ZcUrl
	 */
	static function getUrl() {
		return self::singleton('ZcUrl');
	}
}