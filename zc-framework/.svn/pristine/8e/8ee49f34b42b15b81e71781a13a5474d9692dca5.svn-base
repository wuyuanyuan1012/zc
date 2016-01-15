<?php
/**
 * 系统的监控。记录系统运行中的任何报错、异常、错误关闭等信息。
 * 
 * @author jianhui.tangjh 2012-06-19 14:30
 *
 */
class ZcMonitorHandler {
	// 当前进程记录的最大数
	protected $maxLogInOneRequest = 0;
	protected $dbLink;
	
	protected  function getErrorContents($errorStr) {
		if (!is_string($errorStr)) {
			$errorStr = "\r\n" . print_r($errorStr, true);
		}
		
		$errorContents = $errorStr;
		$errorContents .= "\r\n <b>hostname:</b> " . php_uname ( 'n' ) . "\r\n";
		$errorContents .= '&split&';
		if (isset($_SESSION)) {
			$errorContents .= "\r\n + - - - - - - - - - - - - - - - - SESSION INFOMATION - - - - - - - - - - - - - - - + \r\n";
			$errorContents .= "\r\n" . print_r($_SESSION, true) . "\r\n";
		}
		$errorContents .= "\r\n + - - - - - - - - - - - - - - - - SERVER INFOMATION - - - - - - - - - - - - - - - + \r\n";
		$errorContents .= "\r\n" . print_r($_SERVER, true) . "\r\n";
		$errorContents .= "\r\n + - - - - - - - - - - - - - - - - DEBUG BACKTRACE - - - - - - - - - - - - - - - - + \r\n";
		//$errorContents .= "\r\n". print_r(debug_backtrace(), true) . "\r\n";
		
		return $errorContents;
	}
	
	public function monitor($errorStr, $errorType = 1, $needNotify = false) {
		// 最多记录5次
		$this->maxLogInOneRequest++;
		if ($this->maxLogInOneRequest == 5 || empty ( $errorStr )) {
			return false;
		}
		
		$dbLink = $this->getDbLink();
		
		$errorLevelId = $errorType;
		$errorLevelValue = $this->getErrorLevelValue($errorLevelId);
		$errorContents = $this->getErrorContents($errorStr);
		$isNotify = $needNotify == true ? 0 : 1;
		$gmtCreate = date('Y-m-d H:i:s', time());
		
		$query = "INSERT INTO `monitor_log`
			(`error_level_id`, `error_level_value`, `error_contents`, `is_notify`, `gmt_create`)
			VALUES ({$errorLevelId}, '" . $dbLink->escape($errorLevelValue) . "', '" . $dbLink->escape ($errorContents) . "', {$isNotify}, '{$gmtCreate}');";
		$dbLink->query($query);
		
		return $dbLink->getLastId();
	}
	
	protected function getDbLink() {
		if ($this->dbLink) {
			return $this->dbLink;
		}
		$config = ZcFactory::getConfig();
		
		$this->dbLink = new ZcDbSimpleMysql($config->get(ZcConfigConst::MonitorDbServer), $config->get(ZcConfigConst::MonitorDbUsername), $config->get(ZcConfigConst::MonitorDbPassword), $config->get(ZcConfigConst::MonitorDbDatabase));
		
		return $this->dbLink;
	}

	protected function getErrorLevelValue($type) {
		if (!defined('E_DEPRECATED')) {
			define('E_DEPRECATED', 8192);
		}
		if (!defined('E_USER_DEPRECATED')) {
			define('E_USER_DEPRECATED', 16384);
		}
		
		$errortypes = array (
				E_ERROR => 'E_ERROR',
				E_WARNING => 'E_WARNING',
				E_PARSE => 'E_PARSE',
				E_NOTICE => 'E_NOTICE',
				E_CORE_ERROR => 'E_CORE_ERROR',
				E_CORE_WARNING => 'E_CORE_WARNING',
				E_COMPILE_ERROR => 'E_COMPILE_ERROR',
				E_COMPILE_WARNING => 'E_COMPILE_WARNING',
				E_USER_ERROR => 'E_USER_ERROR',
				E_USER_WARNING => 'E_USER_WARNING',
				E_USER_NOTICE => 'E_USER_NOTICE',
				E_STRICT => 'E_STRICT',
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED => 'E_DEPRECATED',
				E_USER_DEPRECATED => 'E_USER_DEPRECATED',
				E_ALL => 'E_ALL' 
		);
		
		$db_link = self::getDbLink ();
		$query = "SELECT  `error_level_id`, `error_level_value`, `notify_email`, `gmt_create` FROM `monitor_notify`";
		$data_obj = $db_link->query ( $query );
		$all_data = $data_obj->rows;
		if (sizeof ( $all_data ) > 0) {
			$data = array ();
			foreach ( $all_data as $v ) {
				$key = ( int ) $v ['error_level_id'];
				$errortypes [$key] = $v ['error_level_value'];
			}
		}
		if (isset ( $errortypes [$type] )) {
			return $errortypes [$type];
		}
		return 'E_UNKNOWN';
	}
}
