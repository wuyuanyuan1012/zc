<?php
/**
 * 
 * 基于memcached的cache实现
 * 
 * @author jianhui.tangjh 2011-11-16 23:33:30
 *
 */
class ZcCacheMemcached extends ZcAbstractCache {
	private $memcache;
	private $expire = 3600;
	private $log;
	
	public function __construct($timestamp, $options = '') {
		if (!extension_loaded('memcache')) {
			$this->conntected = false;
			return;
		}
		
		$this->log = Zc::getLog('cache/memcache_cache.log');

		$this->timestamp = $timestamp;

		if (empty($options)) {
			$this->options = array (
					array (
							'host' => '127.0.0.1',
							'port' => 11211 
					) 
				);
		} else {
			$this->options = $options;
		}
		
		$this->memcache = new Memcache;
		foreach($this->options as $serverConfig) {
			$link_result = $this->memcache->addServer($serverConfig['host'], $serverConfig['port']);
			if( $link_result === false ) {
				$this->log->error("$serverConfig[host] : $serverConfig[port] addServer Failed");
			}
		}
		$this->conntected = true;
		
		register_shutdown_function(array($this, 'close'));
	}
	
	public function get($key) {
		if (!$this->isConnected()) {
			return false;
		}
		$key = $this->buildRealKey($key);

		return $this->memcache->get($key);
	}

	
	
	public function set($key, $value, $expire = null) {
		if (!$this->isConnected()) {
			return false;
		}
		
		$key = $this->buildRealKey($key);

		if (is_null($expire)) {
			$expire = $this->expire;
		}

		$ret = $this->memcache->set($key, $value, 0, $expire);
		if (!$ret) {
			$this->log->error("$key " . print_r($value, true) . " set to memcache fail");
		}
		return $ret;
	}

	public function delete($key, $ttl = false) {
		$key = $this->buildRealKey($key);

		if (intval($ttl) > 0) {
			$ret = $this->memcache->delete($key, $ttl);
		} else {
			$ret = $this->memcache->delete($key);
		}

		if (!$ret) {
			$this->log->error("$key " ."delete memcache key fail");
		}
		return $ret;
	}

	public function clear() {
		$ret = $this->memcache->flush();
		if (!$ret) {
			$this->log->error("memcache flush failed!");
		}
		return $ret;
	}

	public function close() {
		Zc::G(' memcache close start');
		$ret = $this->memcache->close();
		if (!$ret) {
			$this->log->error("memcache close failed!");
		}
		Zc::G(' memcache close end');
		return $ret;
	}
}

?>