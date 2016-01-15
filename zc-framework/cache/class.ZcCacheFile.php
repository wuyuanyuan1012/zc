<?php
/**
 * 基于File的Cache实现
 * 
 * @author jianhui.tangjh 2011-11-16 23:33:30
 *
 */
class ZcCacheFile extends ZcAbstractCache {
	private $log;
	protected $prefix = '~@';

	public function __construct($timestamp, $options = '') {
		$this->log = Zc::getLog('cache/file_cache.log');
		
		$this->options = array(
				'temp' => G_CACHING_CACHEFS_DIRECTORY,
				'expire' => 3600,
				'use_subdir' => true,
				'subdir_level' => 2,
				'cache_data_check' => false,
				'cache_data_compress' => false,
		);

		if (!empty($options)) {
			$this->options = array_merge($this->options, $options);
		}

		if (substr($this->options['temp'], -1) != '/') {
			$this->options['temp'] .= '/';
		}
		$this->timestamp = $timestamp;
		$this->options['temp'] .= $this->timestamp . '/';

		$this->init();

		$this->conntected = is_dir($this->options['temp']) && is_writeable($this->options['temp']);
	}

	private function init() {
		$oldMask = umask(0000);

		if (!is_dir($this->options['temp'])) {
			if (!mkdir($this->options['temp'], 0777, true)) {
				
				$this->log->monitor($this->options);
				
				umask($oldMask);
				return false;
			}
		}
		umask($oldMask);
	}

	/**
	 * 取得变量的存储文件名，同时创建分级目录
	 *
	 * @param string $key
	 * @return false | string 会同时创建子目录，如果失败返回false,成功返回路径
	 */
	public function filename($key) {
		$oldMask = umask(0000);

		$filename = '';
		$key = md5($key);

		if ($this->options['use_subdir']) {
			$dir = '';
			for ($i = 0; $i < $this->options['subdir_level']; $i++) {
				$dir .= $key{$i} . '/';
			}
				
			if (!is_dir($this->options['temp']. $dir)) {
				if (false === mkdir($this->options['temp'] . $dir, 0777, true)) {
					$this->log->monitor("$key $filename mkdir fail");
						
					umask($oldMask);
					return false;
				}
			}
			$filename = $dir . $this->prefix . $key . '.php';
		} else {
			$filename = $this->prefix . $key . '.php';
		}

		umask($oldMask);
		return $this->options['temp'] . $filename;
	}

	/**
	 * 读取缓存
	 *
	 * @param string $key
	 *        	缓存Key
	 * @return mixed
	 */
	public function get($key) {
		
		$filename = $this->filename ( $key );
		if (! $this->isConnected () || ! is_file ( $filename )) {
			return false;
		}

		$content = file_get_contents ( $filename );
		if (false === $content) {
			$this->log->error("$key $filename get fail");
			return false;
		}

		$expire = ( int ) substr ( $content, 8, 12 );

		if ($expire != 0 && time () > filemtime ( $filename ) + $expire) {
			if (false === unlink ($filename)) {
				$this->log->error("$key $filename unlink fail");
			}
			return false;
		}

		if ($this->options['cache_data_check']) {
			$check = substr ( $content, 20, 32 );
			$content = substr ( $content, 52, - 3 );
			if ($check != md5 ( $content )) {
				return false;
			}
		} else {
			$content = substr ( $content, 20, - 3 );
		}

		if ($this->options ['cache_data_compress'] && function_exists ( 'gzcompress' )) {
			$content = gzuncompress ( $content );
		}
		$content = unserialize ( $content );
		return $content;
	}

	public function set($key, $value, $expire = null) {
		if (is_null($expire)) {
			$expire = $this->options['expire'];
		}
		//避免有别的地方设置了umask，所以在set之前，先设置为0，然后再恢复
		$oldMask = umask(0000);

		$filename = $this->filename($key);
		$data = serialize($value);

		if ($this->options['cache_data_compress'] && function_exists('gzcompress')) {
			$data = gzcompress($data, 3);
			if ($data === false) {
				$this->log->error("$key $filename gzcompress fail");
				return false;
			}
		}

		if ($this->options['cache_data_check']) {
			$check = md5($data);
		} else {
			$check = '';
		}

		$data = "<?php\n//" . sprintf('%012d', $expire) . $check . $data . "\n?>";
		$result = file_put_contents($filename, $data);
		umask($oldMask);
		if ($result) {
			clearstatcache();
			return true;
		} else {
			$this->log->error("$key $filename file_put_contents fail");
			return false;
		}
	}

	public function delete($key) {
		$filename = $this->filename($key);
		if (file_exists ( $filename )) {
			$ret = unlink ( $filename );
			if ($ret === false) {
				$this->log->error ( "$key delete fail" );
			}
		} else {
			$this->log->info ( "$key no exists" );
			$ret = false;
		}
		return $ret;
	}

	public function clear() {
		$path = $this->options['temp'];

		if (false !== ($dir = readdir($path))) {

			while (false !== ($file = readdir($dir))) {
				$check = is_dir( $file );
				if ( !$check )
					unlink( $path . $file );
			}
			closedir( $dir );
			return true;
		}

	}

}
?>