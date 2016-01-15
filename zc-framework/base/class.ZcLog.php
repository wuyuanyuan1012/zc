<?php
/**
 * 
 * Log对象的分装，日志分级等功能
 * 
 * @author tangjianhui 2012-09-25 20:25:00
 *
 */
class ZcLog {
	// LogHandler
	const LOG_HANDLER_DIR_FILE = 'ZcDirFileLogHandler';
	const LOG_HANDLER_FLAT_FILE = 'ZcFlatFileLogHandler';
	const LOG_HANDLER_LOGSTASH_REDIS = 'ZcLogstashRedisLogHandler';
	
	// 日志级别 从上到下，由低到高
	const SQL       = 1;  // SQL：SQL语句 注意只在调试模式开启时有效
	const DEBUG   = 2;  // 调试: 调试信息
	const INFO     = 3;  // 信息: 程序输出信息
	const NOTICE  = 4;  // 通知: 程序可以运行但是还不够完美的错误
	const WARN    = 5;  // 警告性错误: 需要发出警告的错误
	const ERR       = 6;  // 一般错误: 一般性错误
	const CRIT      = 7;  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
	const ALERT    = 8;  // 警戒性错误: 必须被立即修改的错误
	const EMERG   = 9;  // 严重错误: 导致系统崩溃无法使用

	private $tags = array(1 => 'SQL', 2 => 'DEBUG', 3 => 'INFO', 4 => 'NOTICE', 5 => 'WARN', 6 => 'ERR', 7 => 'CRIT', 8 => 'ALERT', 9 => 'EMERG');
	
	// 日期格式
	private $format =  '[c]';
	
	/**
	 * @var ZcLogHandler
	 */
	private $logHandler;
	
	// 默认LogLevel
	private $defaultLevel;
	
	// 是否直接输出
	private $echo;
	
	public function __construct($logName = '', $defaultLevel = ZcLog::INFO, $echo = false, $logHandler = ZcLog::LOG_HANDLER_DIR_FILE, $options = array()) {
		
		$this->logHandler = new $logHandler($logName, $options);
		$this->defaultLevel = $defaultLevel;
		
		$this->echo = false;
		if (defined('G_RUNTIME_MODE') && G_RUNTIME_MODE == 'dev') {
			$this->echo = $echo;
		}
	}
	
	/**
	 * 
	 * 记录Log信息及其日志信息
	 * 
	 * @param unknown_type $message
	 * @param unknown_type $level
	 */
	public function log($message, $level) {
		$now = date($this->format);
		$client_ip = $this->getClientIp();
		$levelTag = $this->tags[$level];
		if (!is_string($message)) {
			$message = "\r\n" . print_r($message, true);
		}

		if ($level >= $this->defaultLevel) {
			$this->logHandler->log("{$now} {$client_ip} {$levelTag}: {$message}\r\n");
		}
		if ($this->echo) {
			echo "{$now} {$client_ip} {$levelTag}: {$message} <br />";
		}
	}

	/**
	 * 判断想要记得log级别是否高于默认log级别。
	 * 这是为了避免默认log级别设置的太高，白准备log数据了，毕竟组装要记录的log也是需要时间的嘛
	 * 
	 * @param unknown_type $level 想要记录的log级别
	 */
	public function isEnableLogLevel($level) {
		return 	$level >= $this->defaultLevel;
	}
	
	/**
	 * 记录错误级别的log
	 * @param unknown_type $message
	 */
	public function error($message) {
		$this->log($message, self::ERR);
	}

	/**
	 * 
	 * 记录notice级别的log
	 * @param unknown_type $message
	 */
	public function notice($message) {
		$this->log($message, self::NOTICE);
	}

	/**
	 * 
	 * 记录警告信息
	 * @param unknown_type $message
	 */
	public function warn($message) {
		$this->log($message, self::WARN);
	}
	
	/**
	 * 
	 * 记录普通信息
	 * @param unknown_type $message
	 */
	public function info($message) {
		$this->log($message, self::INFO);
	}
	
	/**
	 * 记录调试信息，只在开发、测试环境开启，禁止在正式环境输出Debug级别的log
	 * @param unknown_type $message
	 */
	public function debug($message) {
		$this->log($message, self::DEBUG);
	}
	
	/**
	 * 
	 * 当有些问题，严重程度到需要通知相关人等时候，需要采用这个方法。
	 * $errorType决定了需要通知哪些人,在yalladmin/monitor_log.php里配置
	 * 这是个很简单，但是非常好用的方法。
	 * 
	 * @param unknown_type $message
	 * @param unknown_type $errorType
	 * @param unknown_type $needNotify 是否需要发送邮件
	 */
	public function monitor($message, $errorType = 1, $needNotify=false) {
		$this->log($message, self::EMERG);
		ZcMonitor::monitor($message, $errorType, $needNotify);
	}
	
	/**
	 * 兼容老的log方法，不要再调用此方法
	 * 
	 * @deprecated
	 */
	public function save() {
		$this->log('in deprecated save function', self::CRIT);
	}
	
	private function getClientIp() {
		static $realip = '';
		if (!empty($realip)) {
			return $realip;
		}
		
		if (isset ( $_SERVER )) {
			if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
				$realip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
			} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
				$realip = $_SERVER ['HTTP_CLIENT_IP'];
			} else {
				if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
					$realip = $_SERVER ['REMOTE_ADDR'];
				} else {
					$realip = '0.0.0.0';
				}
			}
		} else {
			if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
				$realip = getenv ( 'HTTP_X_FORWARDED_FOR' );
			} elseif (getenv ( 'HTTP_CLIENT_IP' )) {
				$realip = getenv ( 'HTTP_CLIENT_IP' );
			} else {
				$realip = getenv ( 'REMOTE_ADDR' );
			}
		}
		
		return $realip;
	}
}