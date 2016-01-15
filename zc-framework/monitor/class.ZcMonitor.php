<?php
/**
 * 系统的监控。记录系统运行中的任何报错、异常、错误关闭等信息。
 * 
 * @author jianhui.tangjh 2012-06-19 14:30
 *
 */
class ZcMonitor {
	
	/**
	 * 记录严重的错误，可能会触发邮件报警等，取决于具体的handler实现。系统内置了一个handler实现
	 * 
	 * @var ZcMonitorHandler
	 */
	static private $monitorHandler;
	
	/**
	 * 记录一般的、或严重的问题
	 * 
	 * @var ZcLog
	 */
	static private $log;
	
	static public function setHandler($handler) {
		self::$monitorHandler = $handler;
	}
	
	static function monitor($errorStr, $errorType = 1, $needNotify = false) {
		if (!self::$monitorHandler) {
			return false;
		}
		self::$monitorHandler->monitor($errorStr, $errorType, $needNotify);
	}
	
	/**
	 * 当程序有未捕获的异常，执行此函数处理
	 * @param Exception $e
	 */
	static public function appException($e) {
		self::monitor($e->__toString());
	}
	
	/**
	 * 当有用户定义的错误发生时，调用此函数
	 * 
	 * 
	 * @param  $errno
	 * @param  $errstr
	 * @param  $errfile
	 * @param  $errline
	 */
	static public function appError($errno, $errstr, $errfile, $errline) {
		$needMonitor = ($errno != E_STRICT) && (($errno & ~E_WARNING & ~E_NOTICE) > 0);
		
		$errorStr = '';
		if ($needMonitor) {
			$errorStr =  "[$errno] [ZcMonitor::appError $errstr] [$errfile] [Line $errline].";
		}
		
		//对于E_STRICT、E_WARNING、E_NOTICE不调用监控
		if ($needMonitor) {
			self::monitor($errorStr, $errno);
		}
	}

	/**
	 * 对于如下异常：E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING，
	 * 需要register_shutdown_function，调用error_get_last才能捕获
	 * 
	 * 当程序异常退出时，调用此函数
	 */
	static public function appShutdown() {
		$error = error_get_last();
		if ($error) {
			self::appError($error['type'], 'ZcMonitor::appShutdown ' . $error['message'], $error['file'], $error['line']);
		} 
	}
}
