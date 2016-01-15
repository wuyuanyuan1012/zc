<?php
/**
 * 
 * Log对象的分装，日志分级等功能
 * 
 * @author tangjianhui 2012-09-25 20:25:00
 *
 */
class ZcLogHandler {
	
	public function log($message) {
		
	}
	
	/**
	 * 递归创建目录
	 *
	 * @param unknown_type $logPath
	 * @param unknown_type $chmod
	 */
	protected function mkdir($logPath, $chmod = 0777) {
		return is_dir($logPath) || ($this->mkdir(dirname($logPath),$chmod) && mkdir($logPath, $chmod));
	}
}