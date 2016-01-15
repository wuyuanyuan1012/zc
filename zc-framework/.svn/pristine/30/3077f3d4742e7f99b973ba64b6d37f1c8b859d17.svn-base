<?php
/**
 * 
 * Log对象的分装，日志分级等功能
 * 
 * @author tangjianhui 2012-09-25 20:25:00
 *
 */
class ZcDirFileLogHandler extends ZcLogHandler {
	private $logFile;
	
	function __construct($logName, $options = array()) {
		$logName = trim($logName);
		if (empty($logName)) {
			$logName = 'zc';
		}
		if (stripos($logName, '.log') === false) {
			$logName .= '.log';
		}
		if ($logName[0] != '/' && $logName[1] != ':') {
			$logDir = !empty($options['logDir']) ? $options['logDir'] : Zc::C(ZcConfigConst::LogDir);
			$logName = rtrim($logDir, '/') . '/'  . $logName;
		}	
		
		$logFile = $logName . '.' . date('Y-m-d', time());
		$this->logFile = $logFile;
		
		$logDir = dirname($logFile);
		$this->mkdir($logDir);
	}
	
	/**
	 * 
	 * 记录Log信息及其日志信息
	 * 
	 * @param string $message
	 */
	public function log($message) {
		error_log($message, 3, $this->logFile);
	}
}