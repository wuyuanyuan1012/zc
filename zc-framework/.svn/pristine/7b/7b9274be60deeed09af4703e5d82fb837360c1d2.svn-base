<?php
/**
 * 
 * 为了配合Logstash，把log发送到redis
 * 
 * @author tangjianhui 2012-09-25 20:25:00
 *
 */
class ZcLogstashRedisLogHandler extends ZcFlatFileLogHandler {
	
	private static $redis = null;
	private static $isSupportedRedis = false;
	private static $isEnable = false;
	
	private $logName;
	private $redisKey;
	
	public function __construct($logName, $options = array()) {
		parent::__construct($logName, $options);
		
		self::$isSupportedRedis = class_exists("Redis");
		self::$isEnable = Zc::C(ZcConfigConst::LogHandlerLogstashredisEnable);
		
		$this->logName = $logName;
		$this->redisKey = Zc::C(ZcConfigConst::LogHandlerLogstashredisKey);
	}
	
	/**
	 * 
	 * 记录Log信息及其日志信息
	 * 
	 * @param string $message
	 */
	public function log($message) {
		parent::log($message);
		
		if (!self::$isEnable) {
			return ;
		}
		
		if (self::$isSupportedRedis && !self::$redis) {
			self::$redis = new Redis();

			try {
				self::$isSupportedRedis = self::$redis->connect(Zc::C(ZcConfigConst::LogHandlerLogstashredisHost), Zc::C(ZcConfigConst::LogHandlerLogstashredisPort), 1);
			} catch (Exception $ex) {
				self::$isSupportedRedis = false;
				parent::log(print_r($ex, true));
			}
		}
		
		if (self::$isSupportedRedis) {
			self::$redis->rPush($this->redisKey, '[' . $this->logName . '] ' . $message);
		}
	}
}