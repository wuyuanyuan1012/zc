<?php
/**
 * 调试的用途。让读写、删除失败，避免在开发、测试过程中一直纠结删缓存。
 * 
 * @author jianhui.tangjh 2011-11-16 23:33:30
 *
 */
class ZcCacheDebug extends ZcAbstractCache {
	private $expire = 3600;
	private $log;
	
	public function __construct($timestamp, $options = '') {
		$this->log = Zc::getLog('cache/debug_cache.log');
		$this->timestamp = $timestamp;
	}
	
	public function get($key) {
		return false;
	}
	
	public function set($key, $value, $expire = null) {
		return false;
	}

	public function delete($key, $ttl = false) {
		return false;
	}

	public function clear() {
		return false;
	}

	public function close() {
		return false;
	}
}

?>