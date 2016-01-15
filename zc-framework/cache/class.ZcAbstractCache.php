<?php
/**
 * 缓存封装
 * 
 * ZcAbstractCache 缓存的抽象类
 *    -- ZcCacheFile 基于文件的缓存实现
 *    -- ZcCacheMemcache  基于Memcaced的缓存实现
 * 
 * @author jianhui.tangjh 2011-11-16 23:33:30
 *
 */
abstract class ZcAbstractCache {
	protected $conntected;
	protected $options = array();
	protected $timestamp = '1';

	protected function buildRealKey($key) {
		return $this->timestamp . $key;
	}

	/**
	 * 获取指定$key的缓存数据
	 * @param $key
	 * @return 缓存存在，返回数据；缓存不存在或超时，返回false
	 */
	abstract public function get($key);

	/**
	 *
	 * 存储对应$key的缓存数据
	 * @param $key
	 * @param $value
	 * @param $expiration 以秒为单位超时时间
	 * @return 存储成功，返回true；存储失败，返回false
	*/
	abstract public function set($key, $value, $expiration = 10);

	/**
	 * 删除指定$key的缓存数据
	 * @param $key
	*/
	abstract public function delete($key);

	/**
	 * 判断当前缓存是否可用
	*/
	public function isConnected() {
		return $this->conntected;
	}

	/*
	 * 有些情况下，有些Cache数据对象本身是个Map，在内存中操作好了，才写到文件里去。
	* 为此封装了如下方法:
	*/

	//尚未保存到缓存里去的内存对象
	private $tempCaches = array();

	/**
	 *
	 * 开始处理指定$key的Cache数据。如果这是个全新的Cache，那么就定义一个空数组
	*/
	public function beginCache($key) {
		$cacheData = $this->get($key);
		
		if (!$cacheData) {
			$cacheData = array();
		}
		$this->tempCaches[$key] = $cacheData;
		return $cacheData;
	}

	public function getCacheItem($key, $subKey) {
		if (isset($this->tempCaches[$key]) && isset($this->tempCaches[$key][$subKey])) {
			$this->tempCaches[$key][$sub_key];
		} else {
			return false;
		}
	}

	public function setCacheItem($key, $subKey, $subValue) {
		if (!isset($this->tempCaches[$key])) {
			return false;
		}

		$this->tempCaches[$key][$subKey] = $subValue;
		return true;
	}

	public function saveCache($key, $expire = 3600) {
		$cacheData = $this->tempCaches[$key];
		unset($this->tempCaches[$key]);
		return $this->set($key, $cacheData, $expire);
	}
}