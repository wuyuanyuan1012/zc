<?php
/**
 * <p>代表到数据库的一个连接</p>
 * <p>所有的错误都会抛出异常ZcDbException，至于是否给最终客户是异常还是fasle，</p>
 *
 * @author tangjianhui 2013-8-14 上午8:52:49
 *
 */
abstract class ZcDbConnection {
	// 返回查询结果集为关联数组
	const RESULT_ASSOC = 1;
	// 返回查询结果集为数字索引
	const RESULT_NUM = 2;
	// 返回关联数组、数字索引都有的数组
	const RESULT_BOTH = 3;
	// 返回驱动原生的返回值
	const RESULT_RAW = 4;
	
	protected $config;
	protected $queryStr; //当前SQL
	protected $affectedRows;
	protected $lastInsertID;
	protected $link;
	protected $connected;  //是否已经连接数据库
	
	protected $transactionActive = false; 
	protected $savepointsSupported = false;
	protected $savepointCounter = 0;
	protected $rollbackOnly = false;
	
	/**
	 * ZcSqlBuilder对象
	 * @var ZcSqlBuilder
	 */
	protected $sqlBuilder;

	abstract public function connect();
	
	/**
	 * 执行insert、update等更新语句，返回受影响的行数
	 * @param string $sql
	 * @return integer affect rows受影响的行数
	 */
	abstract public function exec($sql);
	abstract public function query($sql, $resultType = self::RESULT_ASSOC);
	abstract public function close();
	abstract public function escape($str);
	
	abstract public function startTransaction($isolationLevel);
	abstract public function commit();
	abstract public function rollback();
	/**
	 * @return boolean 是否支持savepoints
	 */
	abstract public function supportsSavepoints();
	
	protected function clearTransactionInfo() {
		$this->transactionActive = false;
		$this->savepointCounter = 0;
		$this->rollbackOnly = false;
	}
	
	
	/**
	 * 当前链接是否已有事务
	 * @return boolean
	 */
	public function isTransactionActive() {
		return $this->transactionActive;
	}
	
	public function setRollbackOnly() {
		$this->rollbackOnly = true;
	}
	
	public function isRollbackOnly() {
		return $this->rollbackOnly;
	}
	
	/**
	 * @return string|boolean 生成成功返回当前savepoint的名字，失败返回false 
	 */
	abstract public function createSavepoint();
	abstract public function rollbackToSavepoint($savepoint);
	abstract public function releaseSavepoint($savepoint);
	
	/**
	 * 返回ZcSqlBuilder对象
	 * 
	 * @return ZcSqlBuilder
	 */
	public function getSqlBuilder() {
		if (!$this->sqlBuilder) {
			$this->sqlBuilder = new ZcSqlBuilder($this);
		}
		return $this->sqlBuilder;
	}
	
	/**
	 * 是主库还是从库
	 */
	public function isMasterRole() {
		return $this->config['role'] == 'master';	
	}
	
	public function getDbId() {
		return $this->config['db_id'];
	}
	
	abstract public function getServerInfo();
	
	/**
	 * 当执行完insert语句，可以通过该方法得到最后插入的ID
	 * 
	 * @return integer last insert id
	 */
	public function lastInsertId() {
		return $this->lastInsertID;
	}
	
	public function affectedRows() {
		return $this->affectedRows;
	}
}