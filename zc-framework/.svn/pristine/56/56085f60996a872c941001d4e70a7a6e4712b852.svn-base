<?php
/**
 * 
 * 扁平化的Log封装
 * 
 * @author tangjianhui 2012-09-25 20:25:00
 *
 */
class ZcFlatFileLogHandler extends ZcLogHandler {
	private $logFile;
	
	public function __construct($logName, $options = array()) {
		$logName = trim($logName);
		if (empty($logName)) {
			$logName = 'zc';
		}
		if (stripos($logName, '.log') === false) {
			$logName .= '.log';
		}
		$logName = str_replace(array('/', '\\'), '-', $logName);
		
		$logDir = !empty($options['logDir']) ? $options['logDir'] : Zc::C(ZcConfigConst::LogDir);
		$logDir = rtrim($logDir, '/') . '/';
		
		$today = date('Y-m-d', time());
		$yesterday = date('Y-m-d', time() - 24 * 3600);
		
		$todayLogDir = $logDir . $today . '/';
		$yesterdayLogDir = $logDir . $yesterday . '/';
		
		$this->logFile = $todayLogDir . $logName;
		$this->mkdir($todayLogDir);
		
		// 加这个标识，纯粹就是为了让logstash能够得到multiline
		if (!file_exists($this->logFile)) {
			$yesterdayLogFile = $yesterdayLogDir . $logName;
			error_log(date("[c]") . " -- End $yesterday Log --\r\n", 3, $yesterdayLogFile);
			error_log(date("[c]") . " -- Start $today Log --\r\n", 3, $this->logFile);
		}
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