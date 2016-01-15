<?php
/**
 * 默认ZcDb的事件监听器
 * 
 * @author tangjianhui 2013-9-1 上午11:37:38
 *
 */
class ZcDbDefaultListener extends ZcDbListener{
	
	CONST SCALE = 6;
	
	private $sqlBuildStats = array();
	private $sqlBuildStartTime;  
	
	private $sqlExecStats = array();
	private $sqlExecStartTime;  //每条SQL的开始执行时间
	
	private $cacheGetStats = array();
	private $cacheGetStartTime;
	

	private $cacheSetStats = array();
	private $cacheSetStartTime;
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function beforeBuildSql($db, $args) {
		$this->sqlBuildStartTime = ZcNumberHelper::microtimeFloat(self::SCALE);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function afterBuildSql($db, $args) {
		$sqlBuildTime = bcsub(ZcNumberHelper::microtimeFloat(self::SCALE), $this->sqlBuildStartTime, self::SCALE);
		$dbId = $args['dbId'] ? $args['dbId'] : 'default';
		
		$this->sqlBuildStats[$dbId][] = array($args, $sqlBuildTime);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function beforeExecSql($db, $args) {
		$this->sqlExecStartTime = ZcNumberHelper::microtimeFloat(self::SCALE);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function afterExecSql($db, $args) {
		$sqlExecTime = bcsub(ZcNumberHelper::microtimeFloat(self::SCALE), $this->sqlExecStartTime, self::SCALE);
		$dbId = $args['dbId'] ? $args['dbId'] : 'default';
		$this->sqlExecStats[$dbId][] = array($args['sql'], $sqlExecTime); 
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function beforeGetCache($db, $args) {
		$this->cacheGetStartTime = ZcNumberHelper::microtimeFloat(self::SCALE);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function afterGetCache($db, $args) {
		$cacheGetTime = bcsub(ZcNumberHelper::microtimeFloat(self::SCALE), $this->cacheGetStartTime, self::SCALE);
		$this->cacheGetStats[] = array($args, $cacheGetTime);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function beforeSetCache($db, $args) {
		$this->cacheSetStartTime = ZcNumberHelper::microtimeFloat(self::SCALE);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function afterSetCache($db, $args) {
		$cacheSetTime = bcsub(ZcNumberHelper::microtimeFloat(self::SCALE), $this->cacheSetStartTime, self::SCALE);
		$this->cacheSetStats[] = array($args, $cacheSetTime);
	}
	
	/**
	 * @param ZcDb $db
	 * @param array $args
	 */
	public function error($db, $args) {
		//monitor to log
	}
	
	public function getSqlExecStats() {
		return $this->sqlExecStats;
	}
	
	public function getSqlBuildStats() {
		return $this->sqlBuildStats;
	}
	
	public function getCacheGetStats() {
		return $this->cacheGetStats;
	}
	
	public function getCacheSetStats() {
		return $this->cacheSetStats;
	}
	
	private function getSqlType($sql) {
		$sql_type = substr(trim($sql), 0, 6);
		return strtolower ( $sql_type );
	}
	
	/**
	 * 返回通用的统计结果
	 */
	public function getTotalStats() {
		$ret = array();
		
		$allTime = 0;
		
		$execStats = array();
		foreach($this->sqlExecStats as $dbId => $dbExecStat) {
			foreach($dbExecStat as $one) {
				$this->getSqlType($one[0]) == 'select' ? $execStats[$dbId]['readCount']++ : $execStats[$dbId]['writeCount']++; 
				$this->getSqlType($one[0]) == 'select' ? ($execStats[$dbId]['readTime'] = bcadd($execStats[$dbId]['readTime'], $one[1], self::SCALE)) : ($execStats[$dbId]['writeTime'] = bcadd($execStats[$dbId]['writeTime'], $one[1], self::SCALE));
				$allTime = bcadd($allTime, $one[1], self::SCALE);
			}
		}
		$ret['execStats'] = $execStats;
		
		$buildStats = array();
		foreach($this->sqlBuildStats as $dbId => $dbBuildStat) {
			foreach($dbBuildStat as $one) {
				$buildStats[$dbId] = bcadd($buildStats[$dbId], $one[1], self::SCALE);
				$allTime = bcadd($allTime, $one[1], self::SCALE);
			}
		}
		$ret['buildStats'] = $buildStats;
		
		$getCacheStats = array();
		foreach($this->cacheGetStats as $one) {
			$getCacheStats['getCount']++;
			$getCacheStats['getTime'] = bcadd($getCacheStats['getTime'], $one[1], self::SCALE);
			$allTime = bcadd($allTime, $one[1], self::SCALE);
		}
		$ret['getCacheStats'] = $getCacheStats;
		
		$setCacheStats = 0;
		foreach($this->cacheSetStats as $one) {
			$setCacheStats['setCount']++;
			$setCacheStats['setTime'] = bcadd($setCacheStats['setTime'], $one[1], self::SCALE);
			$allTime = bcadd($allTime, $one[1], self::SCALE);
		}
		$ret['setCacheStats'] = $setCacheStats;
		
		$ret['allTime'] = $allTime;
		
		return $ret;
	}
}