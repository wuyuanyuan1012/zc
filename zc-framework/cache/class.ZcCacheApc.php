<?php
/**
 * 基于Apc的Cache实现
 * 
 * @author jianhui.tangjh 2011-11-16 23:33:30
 *
 */
class ZcCacheApc extends  ZcAbstractCache {
	private $log;
	
	public function __construct($timestamp, $options = '') {
		$this->log = Zc::getLog('cache/apc_cache.log');
		
		if (!function_exists('apc_cache_info') || apc_cache_info() === false) {
			$this->log->info("apc module cound not found or disable");
			
			$this->conntected = false;
		} else {
			$this->conntected = true;
		}

		$this->options = array(
				'expire' => 3600,
		);

		if (!empty($options)) {
			$this->options = array_merge($this->options, $options);
		}

		$this->timestamp = $timestamp;
	}

	public function get($key) {
		
		$key = $this->buildRealKey($key);

		if (!$this->isConnected()) {
			return false;
		}
		$ret =  apc_fetch($key);
		if ($ret === false) {
			$this->log->info("$key get failed");
		}
		return $ret;
	}

	public function set($key, $value, $expire = null) {
		$key = $this->buildRealKey($key);

		if (!$this->isConnected()) {
			return false;
		}

		if (is_null($expire)) {
			$expire = $this->options['expire'];
		}
		$ret =  apc_store($key, $value, $expire);
		if ($ret === false) {
			$this->log->info("$key set failed");
		}
		return $ret;
	}

	public function delete($key) {
		$key = $this->buildRealKey($key);

		if (!$this->isConnected()) {
			return false;
		}

		$ret =  apc_delete($key);
		if ($ret === false) {
			$this->log->info("$key delete failed");
		}
		return $ret;
	}

	public function clear() {
		if (!$this->isConnected()) {
			return false;
		}
		return apc_clear_cache('user');
	}
}
?>