<?php
/**
 * ZcSessionHandler的抽象类
 * 
 * @author tangjianhui 2013-6-29 下午4:11:28
 *
 */
abstract class ZcSessionHandler {
	protected $sessionLog;
	protected $lifeTime = 1440;

	protected function getLiftTime() {
		$lifeTime = 1440;
		if (!$lifeTime = get_cfg_var('session.gc_maxlifetime')) {
			$lifeTime = 1440;
		}

		return $lifeTime;
	}

	public function __construct() {
		$this->lifeTime = $this->getLiftTime();
		$this->sessionLog = Zc::getLog('session/handler.log', ZcLog::INFO, false);
	}

	abstract public function open($save_path, $session_name);
	abstract public function close();
	abstract public function read($key);
	abstract public function write($key, $val);
	abstract public function destroy($key);
	abstract public function gc($maxlifetime);
}
