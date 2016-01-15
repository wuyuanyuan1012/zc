<?php
/**
 * 初始化框架Session
 *
 * Session的开始、重新创建、获取Session Id和Name等相关操作的类。
 * 当前这个类都是静态的方法和属性。也就是说，在当前系统内，只有一份。
 *
 * @author tangjianhui 2012-11-13 11:20:11
 */
class ZcSession {
	private static $sessionHander;
	private static $isStart = false;
	private static $log;

	public static function startSessionWithParams($sessionName = '', $sessionDomain = '', $sessionId = '', $sessionType = 'file', $options = array()) {
// 		Zc::dump($sessionName);
// 		Zc::dump($sessionDomain);
// 		Zc::dump($sessionId);
// 		Zc::dump($sessionType);
// 		Zc::dump($options);

		self::$log = Zc::getLog('session/manager.log');

		if (!empty($sessionName)) {
			session_name($sessionName);
		}

		session_set_cookie_params(0, '/', !empty($sessionDomain) ? $sessionDomain : '');

		if ((!empty($sessionId))) {
			session_id($sessionId);
		}

		if ($sessionType === 'memcached' || $sessionType == 'db') {

			$handlerClassName = 'Zc' . ucfirst($sessionType) . 'SessionHandler';
			self::$sessionHander = new $handlerClassName($options);

			session_set_save_handler(
			array(self::$sessionHander, 'open'),
			array(self::$sessionHander, 'close'),
			array(self::$sessionHander, 'read'),
			array(self::$sessionHander, 'write'),
			array(self::$sessionHander, 'destroy'),
			array(self::$sessionHander, 'gc')
			);

			register_shutdown_function('session_write_close');
		} elseif ($sessionType === 'file') {
			if (!empty($options['session_save_path'])) {
				session_save_path($options['session_save_path']);
			}
		} else {
			throw new Exception("do not support session type $sessionType");
		}

		$ret = session_start();
		if (!$ret) {
			self::$log->monitor("$sessionType session start failed");
		}

		self::$isStart = $ret;

		if (!isset($_SESSION['securityToken'])) {
			$_SESSION['securityToken'] = md5(uniqid(rand(), true));
		}
	}

	public static function recreateSession() {
		session_regenerate_id();
	}

	public static function sessionDestroy() {
		$sessionId = session_id();
		if(!empty($sessionId)) {
			session_unset();
			session_destroy();
		}
	}

	public static function getSessionId() {
		if (!self::$isStart) {
			self::$log->monitor('session has not start');
		}
		return session_id();
	}

	public static function getSessionName() {
		if (!self::$isStart) {
			self::$log->info('session has not start');
		}

		return session_name();
	}

	public static function isSessionStart() {
		return isset($_SESSION) && session_id();
	}
}