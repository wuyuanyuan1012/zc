<?php
/**
 * 基于Memcached的高可用主备Session Handler。
 * 两个集群，一个master、一个salve。读的时候，先从master读，读不到，再从slave读；写的时候，两边都写。
 * 这样可以实现，当任意一个集群重启，都不会导致session丢失。那么当master、slave都需要重启，怎么办呢？
 * 答案是：错开20分钟重启（比如Session有效时间是20分钟的话）
 * 
 * @author tangjianhui 2013-6-29 下午5:46:28
 *
 */
class ZcMemcachedSessionHandler extends ZcSessionHandler {
	protected $masterServersConfig;
	protected $masterMemcache = null;
	protected $masterConntected;
	
	protected $slaveServersConfig;
	protected $slaveMemcache = null;
	protected $slaveConntected;

	public function __construct($options = array()) {
		parent::__construct();

		if (empty($options['master_servers'])) {
			$this->masterServersConfig = array (
					array (
							"host" => "127.0.0.1",
							"port" => 11211,
					)
			);
		} else {
			$this->masterServersConfig = $options['master_servers'];
		}

		if (empty($options['slave_servers'])) {
			$this->slaveServersConfig = array (
					array (
							"host" => "127.0.0.1",
							"port" => 11212,
					)
			);
		} else {
			$this->slaveServersConfig = $options['slave_servers'];
		}
	}
	
	public function open($save_path, $session_name) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in session session open, save_path is $save_path, and session_name is $session_name");
		}
		return true;
	}


	public function close() {
		if ($this->masterConntected) {
			$retMaster = $this->masterMemcache->close();
			if (!$retMaster) {
				$this->sessionLog->monitor("master memcache close failed");
			}
		}
		
		if ($this->slaveConntected) {
			$retSlave = $this->slaveMemcache->close();
			if (!$retSlave) {
				$this->sessionLog->monitor("slave memcache close failed");
			}
		}
		
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("master close ret [" . $retMaster . '], slave close ret [' . $retSlave . ']');
		}
	}
	
	public function read($key) {
		
		$this->connectMemcacheServer($key);
		
		if ($this->masterConntected) {
			$value = $this->masterMemcache->get($key);
			if (!$value) {
				$this->sessionLog->info("$key can not get data from master. this is maybe session expire or have some error, try slave.");
			} else {
				if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
					$this->sessionLog->debug("$key get from master, value is $value");
				}
				return $value;
			}
		}
		
		if ($this->slaveConntected) {
			$value = $this->slaveMemcache->get($key);
			if (!$value) {
				$this->sessionLog->info("$key can not get data from slave. this is maybe session expire or have some error.");
			} else {
				$this->sessionLog->warn("$key get from slave, sothing wrong in the master. value is $value");
				return $value;
			}
		}
		
		return $value;
	}
	
	private function connectMemcacheServer($key) {
		$rrKey = ord($key);
		
		//连接Master Memcached
		$masterIndex = $rrKey % count($this->masterServersConfig);
		$targetServerConfig = $this->masterServersConfig[$masterIndex];
		
		$this->masterMemcache = new Memcache;
		G('start connect master memcache');
		$this->masterConntected = $this->masterMemcache->connect($targetServerConfig['host'], $targetServerConfig['port'], 1);
		G('end connect master memcache');
		if (!$this->masterConntected) {
			$this->sessionLog->error("$key connect to master session server " . $targetServerConfig['host'] . ':' . $targetServerConfig['port'] . ' failed, try slave!');
		} else {
			if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
				$this->sessionLog->debug("$key connect to master session server " . 	$targetServerConfig['host'] . ':' . $targetServerConfig['port']  . ' success.');
			}
		}
		
		//如果Slave Memcached也连接失败，那么太糟糕了，需要检查运维上有什么问题了
		$slaveIndex = $rrKey % count($this->slaveServersConfig);
		$targetServerConfig = $this->slaveServersConfig[$slaveIndex];
		
		$this->slaveMemcache = new Memcache;
		G('start connect slave memcache');
		$this->slaveConntected = $this->slaveMemcache->connect($targetServerConfig['host'], $targetServerConfig['port'], 1);
		G('end connect slave memcache');
		
		if (!$this->slaveConntected) {
			$this->sessionLog->error("$key connect to slave session server " . $targetServerConfig['host'] . ':' . $targetServerConfig['port'] . ' failed. very terrible.');
		} else {
			if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
				$this->sessionLog->debug("$key connect to slave session server " . $targetServerConfig['host'] . ':' . $targetServerConfig['port'] . ' success.');
			}
		}
		
		if (!$this->masterConntected && !$this->slaveConntected) {
			$this->sessionLog->monitor("connect to master and slave are all failed. [$key]");
		}
	}

	public function write($key, $val) {
		Zc::G('session start write to memcache');
		
		if (empty($val)) {
			$this->sessionLog->info("$key want to write, but $val is empty");
			return ;
		} else {
			if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
				$this->sessionLog->debug("start write [$key] [$val]");
			}
		}
		
		if ($this->masterConntected) {
			$retMaster = $this->masterMemcache->set($key, $val, 0, $this->lifeTime);
			if (!$retMaster) {
				$this->sessionLog->error("$key $val write to master failed. try slave");
			}
		}
		
		if ($this->slaveConntected) {
			$retSlave = $this->slaveMemcache->set($key, $val, 0, $this->lifeTime);
			if (!$retSlave) {
				$this->sessionLog->error("$key $val write to salve failed.");
			}
		}
		
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("write [$key] -> [$val], master write ret [" . $retMaster . '], slave write ret [' . $retSlave . ']');
		}
		
		if (!$retMaster && !$retSlave) {
			$this->sessionLog->error("write to master and slave are all failed. [$key] [$val]");
		}
		
		Zc::G('session end write to memcache');
	}

	public function destroy($key) {
		if ($this->masterConntected) {
			$ret = $this->masterMemcache->delete($key);
			if (!$ret) {
				$this->sessionLog->error("$key destroy from master failed. try slave");
			}
		}
		
		if ($this->slaveConntected) {
			$ret = $this->slaveMemcache->delete($key);
			if (!$ret) {
				$this->sessionLog->error("$key destroy from salve failed.");
			}
		}
	}

	public function gc($maxlifetime) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in memcache session, no need gc method");
		}
	}

}
