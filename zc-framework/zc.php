<?php
/**
 * Zc 2.0的入口文件
 *
 * @author tangjianhui 2013-6-28 下午3:42:28
 *
 */
class Zc {
	// Zc框架文件所在的根目录
	private static $zcFrameworkFsRootDir;

	private static $importFiles = array();

	// Zc框架本身的初始化是否已经完成
	private static $isZcFrameworkAutoload = false;

	// Zc内部类
	private static $zcClassMapping = array();

	public static function zcFrameworkAutoloader($class) {
		if (isset(self::$zcClassMapping[$class])) {
			$file = self::$zcFrameworkFsRootDir . self::$zcClassMapping[$class];
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}

	/**
	 * 初始化Zc框架的内部类的自动加载机制
	 */
	public static function initZcFrameworkAutoloader() {
		if (self::$isZcFrameworkAutoload) {
			return ;
		}

		self::$zcFrameworkFsRootDir = dirname(__FILE__) . '/';

		self::$zcClassMapping = array(
				'ZcNumberHelper' => 'helpers/class.ZcNumberHelper.php',
				'ZcStringHelper' => 'helpers/class.ZcStringHelper.php',
				'ZcHtmlHelper' => 'helpers/class.ZcHtmlHelper.php',
				'ZcUrlHelper' => 'helpers/class.ZcUrlHelper.php',
				'ZcArrayHelper' => 'helpers/class.ZcArrayHelper.php',
				
				'ZcIInit' => 'base/class.ZcIInit.php',
				'ZcConfigConst' => 'base/class.ZcConfigConst.php',
				'ZcAutoloader' => 'base/class.ZcAutoloader.php',
				'ZcConfig' => 'base/class.ZcConfig.php',
				'ZcFactory' => 'base/class.ZcFactory.php',
				'ZcLanguage' => 'base/class.ZcLanguage.php',
				'ZcLog' => 'base/class.ZcLog.php',
				'ZcLogHandler' => 'base/class.ZcLogHandler.php',
				'ZcDirFileLogHandler' => 'base/log-handlers/class.ZcDirFileLogHandler.php',
				'ZcFlatFileLogHandler' => 'base/log-handlers/class.ZcFlatFileLogHandler.php',
				'ZcLogstashRedisLogHandler' => 'base/log-handlers/class.ZcLogstashRedisLogHandler.php',
				'ZcEscaper' => 'base/class.ZcEscaper.php',

				'ZcAbstractCache' => 'cache/class.ZcAbstractCache.php',
				'ZcCacheApc' => 'cache/class.ZcCacheApc.php',
				'ZcCacheDebug' => 'cache/class.ZcCacheDebug.php',
				'ZcCacheFile' => 'cache/class.ZcCacheFile.php',
				'ZcCacheMemcached' => 'cache/class.ZcCacheMemcached.php',

				'ZcDb' => 'db/class.ZcDb.php',
				'ZcDbConnection' => 'db/class.ZcDbConnection.php',
				'ZcSqlBuilder' => 'db/class.ZcSqlBuilder.php',
				'ZcDbSimpleMysql' => 'db/class.ZcDbSimpleMysql.php',
				'ZcDbEval' => 'db/class.ZcDbEval.php',
				'ZcDbListener' => 'db/class.ZcDbListener.php',
				'ZcDbDefaultListener' => 'db/class.ZcDbDefaultListener.php',
				'ZcMysqlDbConnection' => 'db/drivers/class.ZcMysqlDbConnection.php',
				'ZcTransactionDefinition' => 'db/transaction/class.ZcTransactionDefinition.php',
				'ZcTransactionStatus' => 'db/transaction/class.ZcTransactionStatus.php',
				'ZcDbException' => 'db/exceptions/class.ZcDbException.php',
				'ZcDbConnectionException' => 'db/exceptions/class.ZcDbConnectionException.php',

				'ZcMonitor' => 'monitor/class.ZcMonitor.php',
				'ZcMonitorHandler' => 'monitor/class.ZcMonitorHandler.php',

				'ZcAction' => 'web/class.ZcAction.php',
				'ZcController' => 'web/class.ZcController.php',
				'ZcDispatcher' => 'web/class.ZcDispatcher.php',
				'ZcSession' => 'web/class.ZcSession.php',
				'ZcSessionHandler' => 'web/class.ZcSessionHandler.php',
				'ZcUrl' => 'web/class.ZcUrl.php',
				'ZcUrlHandler' => 'web/class.ZcUrlHandler.php',
				'ZcWidget' => 'web/class.ZcWidget.php',

				'ZcDefaultUrlHandler' => 'web/url-handlers/class.ZcDefaultUrlHandler.php',
				'ZcSimplePathInfoUrlHandler' => 'web/url-handlers/class.ZcSimplePathInfoUrlHandler.php',
				'ZcSimpleRewriteUrlHandler' => 'web/url-handlers/class.ZcSimpleRewriteUrlHandler.php',

				'ZcDbSessionHandler' => 'web/session-handlers/class.ZcDbSessionHandler.php',
				'ZcMemcachedSessionHandler' => 'web/session-handlers/class.ZcMemcachedSessionHandler.php',

				'ZcPagination' => '/web/view-helpers/class.ZcPagination.php',

				'ZcWebUser' => '/web/auth/class.ZcWebUser.php',
				'ZcRbac' => '/web/auth/class.ZcRbac.php',
				'ZcDbRbac' => '/web/auth/class.ZcDbRbac.php',
				'ZcRole' => '/web/auth/class.ZcRole.php',
				'ZcPermission' => '/web/auth/class.ZcPermission.php',
				'ZcIAuthAssertion' => '/web/auth/class.ZcIAuthAssertion.php',
				'ZcRolePartialOrderException' => '/web/auth/class.ZcRolePartialOrderException.php',
				);

		spl_autoload_register(array('Zc', 'zcFrameworkAutoloader'));

		self::$isZcFrameworkAutoload = true;
	}

	/**
	 * 手动加载文件
	 *
	 * @param string $class
	 * @param string $baseUrl
	 * @param string $ext
	 * @param string $prefix
	 * @return multitype:
	 */
	public static function import($class, $baseUrl = '', $ext='.php', $prefix = '') {
		$class = str_replace(array('.', '#'), array('/', '.'), $class);

		// 如果是zc自带的类
		if (empty($baseUrl) && false === strpos($class, '/')) {
			return isset(self::$zcClassMapping[$class]);
		}

		$classStrut = explode('/', $class);
		$realClass = array_pop($classStrut);  //最后一个是类名或文件名
		if (empty($baseUrl)) {
			$dirAlias = array_shift($classStrut); //第一个是目录别名
			if ('@' == $dirAlias) {
				//加载当前项目应用类库
				$baseUrl = Zc::C(ZcConfigConst::DirFsLibs);
			} elseif ('zc' == $dirAlias) {
				// zc 官方基类库
				$baseUrl = self::$zcFrameworkFsRootDir;
			}
		}
		if (substr($baseUrl, -1) != '/') {
			$baseUrl .= '/';
		}
		if (count($classStrut) > 0) {
			$baseUrl .= implode('/', $classStrut) . '/';
		}

		if ($realClass === '*' && (($currDir = dir($baseUrl)) !== false)) {
			while(($currFile = $currDir->read()) !== false) {
				if (preg_match("/{$ext}$/", $currFile) > 0) {
					$classfile = $baseUrl . $currFile;
					self::requireCache($classfile);
				}
			}
			$currDir->close();
		} else {
			$classfile = $baseUrl . $prefix . $realClass . $ext;
			if (!class_exists(basename($realClass), false)) {
				// 如果类不存在 则导入类库文件
				return self::requireCache($classfile);
			}
		}
	}

	private static function requireCache($filename) {
		if (!isset(self::$importFiles[$filename])) {
			if (file_exists($filename)) {
				require $filename;
				self::$importFiles[$filename] = true;
			} else {
				self::$importFiles[$filename] = false;
			}
		}
		return self::$importFiles[$filename];
	}

	/**
	 * 初始化系统配置。
	 * *fs* = Filesystem directories (local/physical)。绝对路径的目录
	 * *ws* = Webserver directories (virtual/URL)。相对于应用的目录
	 *
	 * @param string $rootFsDir 系统根目录，比如/home/www/
	 * @param string $appDir mvc代码相对于系统根目录的位置 比如 front_app/
	 */
	private static function initConfig($rootFsDir, $appDir) {
		$config = ZcFactory::getConfig();

		$rootFsDir = rtrim($rootFsDir, '/') . '/';
		$appDir = ($appDir === '/') ? '' : rtrim($appDir, '/') . '/';

		$appFsDir = $rootFsDir . $appDir;

		$config->set(ZcConfigConst::DirFsRoot, $rootFsDir);
		$config->set(ZcConfigConst::DirFsApp, $appFsDir);
		$config->set(ZcConfigConst::DirFsConf, $appFsDir . 'conf/');
		$config->set(ZcConfigConst::DirFsLanguages, $appFsDir . 'languages/');

		$config->set(ZcConfigConst::DirFsViewsLayout, $appFsDir . 'views/layout/');
		$config->set(ZcConfigConst::DirFsViewsPage, $appFsDir . 'views/page/');
		$config->set(ZcConfigConst::DirFsViewsWidget, $appFsDir . 'views/widget/');
		$config->set(ZcConfigConst::DirWsViewsStatic, $appDir . 'views/static/');

		$config->set(ZcConfigConst::DirFsLibs, $appFsDir . 'libs/');
		$config->set(ZcConfigConst::DirFsLibsController, $appFsDir . 'libs/controller/');
		$config->set(ZcConfigConst::DirFsLibsWidget, $appFsDir . 'libs/widget/');

		$config->set(ZcConfigConst::DirWsApp, $appDir);

		//language
		$config->set(ZcConfigConst::LanguageDefault, 'english');
		$config->set(ZcConfigConst::LanguageCurrent, 'chinese');

		$config->mergeFromFile($config->get(ZcConfigConst::DirFsConf) . 'conf.php');
	}

	private static function initMonitor() {
		$isAutoStart = ZcFactory::getConfig()->get(ZcConfigConst::MonitorAutostart);
		if (!$isAutoStart) {
			return ;
		}
		
		$monitorHandlerConfig = ZcFactory::getConfig()->get(ZcConfigConst::MonitorHandler);
		if (!empty($monitorHandlerConfig['file'])) {
			require_once (Zc::C(ZcConfigConst::DirFsApp) . $monitorHandlerConfig['file']);
			$monitorHandler = new $monitorHandlerConfig['class'];
		} else {
			$monitorHandler = new ZcMonitorHandler();
		}
		ZcMonitor::setHandler($monitorHandler);
		self::setMonitor();
	}

	private static function setMonitor() {
		register_shutdown_function(array('ZcMonitor', 'appShutdown'));
		set_error_handler(array('ZcMonitor','appError'));
		set_exception_handler(array('ZcMonitor','appException'));
	}

	public static function init($rootFsDir, $appDir = '/') {

		// 初始化Zc框架内部类的自动加载机制
		self::initZcFrameworkAutoloader();

		// 初始化配置
		self::initConfig($rootFsDir, $appDir);

		// 初始化默认时区
		self::initTimezone();

		// 初始化监控
		self::initMonitor();

		// 应用类的自动加载
		ZcAutoloader::init();
	}

	public static function mdump() {
		$args = func_get_args();
		foreach($args as $arg) {
			self::dump($arg);
		}
	}
	
    public static function dump($var, $strict=true, $echo=true, $label=null) {
		$label = ($label === null) ? '' : rtrim($label) . ' ';
		if (!$strict) {
			if (ini_get('html_errors')) {
				$output = print_r($var, true);
				$output = "<pre style='white-space: pre-wrap; word-wrap: break-word;'>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
			} else {
				$output = $label . print_r($var, true);
			}
		} else {
			ob_start();
			var_dump($var);
			$output = ob_get_clean();
			if (!extension_loaded('xdebug')) {
				$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
				$output = "<pre style='white-space: pre-wrap; word-wrap: break-word;'>" . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
			}
		}
		if ($echo) {
			echo($output);
			return null;
		}else
			return $output;
	}

	/**
	 * 渲染输出Widget
	 *
	 * @param string $route
	 * @param array $data
	 * @param boolean $return
	 * @return NULL | string
	 */
	public static function W($route, $data=array(), $return=false) {
		$parts = explode('/', $route);

		$lastPart = array_pop($parts);
		$className = str_replace(' ', '', ucwords(strtolower(str_replace(array('-', '_'), ' ', $lastPart)))) . 'Widget';
		$path = '';
		foreach ($parts as $part) {
			$path .= $part . '/';
		}
		//加载语言
		ZcFactory::getLanguageObject()->loadWidgetLanguageByRoute($route);
		require_once (Zc::C(ZcConfigConst::DirFsLibsWidget) . $path . 'class.' . $className . '.php');
		$widget = new $className();
		$content = $widget->render($data);
		if ($return)
			return $content;
		else
			echo $content;
	}

	/**
	 * 获取某个语言常量
	 * @param string $key
	 */
	public static function L($key) {
		return ZcFactory::getLanguageObject()->get($key);
	}

	/**
	 * 得到DB操作类
	 *
	 * @return ZcDb
	 */
	public static function getDb() {
		return ZcFactory::singleton('ZcDb');
	}

	/**
	 * 引用某个语言文件
	 * @param file 绝对路径文件名称
	 * @param lang 语言类型 默认是<b>english</b>
	 * @param string $key
	 */
	public static function loadLanguageFile($file, $lang = 'english') {
		return ZcFactory::getLanguageObject()->loadByFile($file, $lang);
	}

	/**
	 * 如果$key为空，返回所有配置；
	 * 如果$key不为空，$value为空，返回$key的值
	 * 如果$key, $value都不为空，设置属性
	 *
	 * @param String $key
	 * @param String $value
	 * @return String|Array|null
	 */
	public static function C($key = null, $value = null) {
		$config = ZcFactory::getConfig();

		if (empty($key)) {
			return $config->get();
		}

		if (is_string($key)) {
			if (is_null($value)) {
				return $config->get($key);
			} else {
				$config->set($key, $value);
			}
		}
		return null;
	}

	public static function url($route, $param = '',  $scheme = false, $host = false) {
		return ZcFactory::getUrl()->url($route, $param, $scheme, $host);
	}

	public static function singleton($className) {
		return ZcFactory::singleton($className);
	}

	//记录和统计时间（微妙）
	public static function G($start = '', $end = '', $dec = 3) {

		if ( empty($_GET['need_stat']) ) {
			return false;
		}

		static $_info = array();

		if (is_float($end)) {
			$info[$start] = $end;
		} elseif (!empty($end)) {
			if (!isset($_info[$end])) {
				$_info[$end] = ZcNumberHelper::microtimeFloat(3);
			}
			return number_format($_info[$end] - $_info[$start], $dec);
		} elseif (!empty($start)) {
			$_info[$start] = ZcNumberHelper::microtimeFloat(3);
		} else {
			$temp = array();

			$findFirst = true;
			foreach ($_info as $key => $value) {
				if ($findFirst) {
					$baseTime = $value;
					$lastTime = 0;
					$findFirst = false;
				}

				$currTime = bcsub($value, $baseTime, 3);
				$currCostTime = bcsub($currTime, $lastTime, 3);
				$lastTime = $currTime;

				$temp[$key] = $currTime . ' [' . $currCostTime . ']';
			}
			return $temp;
		}
	}

	public static function startMonitor($handler = null) {
		if (empty($handler)) {
			ZcMonitor::setHandler(new ZcMonitorHandler());
		} else {
			ZcMonitor::setHandler($handler);
		}
		self::setMonitor();
	}

	/**
	 * 对于Web应用，返回当前Web应用的唯一用户
	 *
	 * @return ZcWebUser
	 */
	public static function getWebUser() {
		return ZcFactory::singleton('ZcWebUser');
	}

	/**
	 * 返回Log对象
	 *
	 * log文件的位置：
	 * 1， $log_name没有以.log结尾，对于日志文件，会自动加上.log
	 * 2， $log_name后面会自动加上.今天的日期，比如.2012_02_12
	 * 3， 可以用/来自动创建目录，
	 *
	 * 比如调用Zc::getLog('ds/register');那么日志的位置是：Zc::C{log.dir}/ds/register.log.2012_02_12
	 *
	 * @param $logName Log Name
	 * @param $defaultLevel 定义在Log的常量，只有高于默认log级别的log，才会被记录下来
	 * @param $echo 是否输出，如果设置为true，那么当记log的时候，同时会echo到页面中。仅供不懂得用tail -f查看log的懒人调试用，并用于开发环境
	 * @param $logHandler logHandler
	 * @param $options 生成LogHandler的条件
	 *
	 * @return ZcLog
	 */
	public static function getLog($logName = '', $defaultLevel = ZcLog::INFO, $echo = false, $logHandler = ZcLog::LOG_HANDLER_DIR_FILE, $options = array()) {
		return new ZcLog($logName, $defaultLevel, $echo, $logHandler, $options);
	}

	/**
	 * 返回缓存对象实例
	 *
	 * @param string $bizName 缓存的业务标识，这个字段的设置，可以保证缓存对象实现单例
	 * @param string $cacheType
	 * @param string $timestamp
	 * @param array $options
	 * @return ZcAbstractCache
	 */
	public static function getCache($bizName, $cacheType = '',  $timestamp = '', $options = null, $cacheClass = '') {
		static $cacheInstances = array();

		$cacheInstanceKey = $bizName;
		if (!isset($cacheInstances[$cacheInstanceKey])) {
			if (!($cacheClass || $cacheType) || empty ($options)) {
				throw new Exception ("try to create cache[{$bizName}], but cacheType[{$cacheType}], options[" . print_r ($options, true) . "]");
			}

			$systemCacheClass = 'ZcCache' . ucwords($cacheType);
			$cacheClassName = (empty($cacheClass)) ? $systemCacheClass : $cacheClass;
			$cache = new $cacheClassName($timestamp, $options);
			$cacheInstances[$cacheInstanceKey] = $cache;
		}

		return $cacheInstances[$cacheInstanceKey];
	}

	/**
	 * 采用stripslashes反转义特殊字符
	 *
	 * @param array|string $data 待反转义的数据
	 * @return array|string 反转义之后的数据
	 */
	private static function _stripSlashes(&$data) {
		return is_array($data) ? array_map(array('Zc', '_stripSlashes'), $data) : stripslashes($data);
	}

	private static function cleanQuotes() {
		$config = ZcFactory::getConfig();
		if (!$config->get(ZcConfigConst::CleanQuotes)) {
			return;
		}

		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			if (isset($_GET)) $_GET = self::_stripSlashes($_GET);
			if (isset($_POST)) $_POST = self::_stripSlashes($_POST);
			if (isset($_REQUEST)) $_REQUEST = self::_stripSlashes($_REQUEST);
			if (isset($_COOKIE)) $_COOKIE = self::_stripSlashes($_COOKIE);
		}
		//set_magic_quotes_runtime(false);
		if (ini_get('magic_quotes_sybase') != 0) {
			ini_set('magic_quotes_sybase', 0);
		}
	}

	private static function initTimezone() {
		$config = ZcFactory::getConfig();
		$timezone = $config->get(ZcConfigConst::DefaultTimezone);
		if (!empty($timezone)) {
			date_default_timezone_set($timezone);
		}
	}

	/**
	 * 把启动Session作为一个方法暴露出来，这样可以利用整个Zc框架的类自动加载机制，同时也可以作为一个Zc类的一个普通static方法暴露出来使用
	 *
	 * @param unknown_type $sessionName
	 * @param unknown_type $sessionDomain
	 * @param unknown_type $sessionId
	 * @param unknown_type $sessionType
	 * @param unknown_type $options
	 */
	public static function startSessionWithParams($sessionName = '', $sessionDomain = '', $sessionId = '', $sessionType = 'file', $options) {
		self::initZcFrameworkAutoloader();
		ZcSession::startSessionWithParams($sessionName, $sessionDomain, $sessionId, $sessionType, $options);
	}

	/**
	 * Zc框架执行Web MVC的入口函数。
	 *
	 * 我考虑是否把init方法作为私有，而然runMVC来得到rootdir和appdir，调用init来完成初始化框架和应用工作。
	 * 这样其实隐含的一个逻辑，整个Zc框架，可以随着runMVC的参数不同，可以去跑不同的app应用。这个时候还需要把所有的Factory的对象池都清空掉。
	 * 总之，需要把Zc的对象池都清空。
	 */
	public static function runMVC($route = '') {
		//Zc::dump(ZcFactory::getConfig());

		//确保关闭魔术引号
		self::cleanQuotes();

		//URL rewrite
		$zcUrl = ZcFactory::getUrl();
		$zcUrl->parse();

		if (empty($route)) {
			$route = isset($_GET['route']) ? $_GET['route'] : Zc::C(ZcConfigConst::DefaultRoute);
		}
		$action = new ZcAction($route);
		$dispatcher = new ZcDispatcher();
		$dispatcher->dispatch($action);
	}

	public static function sh($string) {
		/* @var $escaper ZcEscaper */
		$escaper = Zc::singleton('ZcEscaper');
		return $escaper->escapeHtml($string);
	}

	public static function shm($string) {
		/* @var $escaper ZcEscaper */
		$escaper = Zc::singleton('ZcEscaper');
		return $escaper->escapeHtmlAttr($string);
	}

	public static function sjs($string) {
		/* @var $escaper ZcEscaper */
		$escaper = Zc::singleton('ZcEscaper');
		return $escaper->escapeJs($string);
	}

	public static function scss($string) {
		/* @var $escaper ZcEscaper */
		$escaper = Zc::singleton('ZcEscaper');
		return $escaper->escapeCss($string);
	}

	public static function surlParam($string) {
		/* @var $escaper ZcEscaper */
		$escaper = Zc::singleton('ZcEscaper');
		return $escaper->escapeUrl($string);
	}
}

Zc::initZcFrameworkAutoloader();