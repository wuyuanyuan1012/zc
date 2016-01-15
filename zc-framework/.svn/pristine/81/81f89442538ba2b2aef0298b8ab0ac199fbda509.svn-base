<?php
/**
 * 事务状态
 * 
 * @author tangjianhui 2013-8-28 上午2:13:29
 *
 */
class ZcTransactionStatus {
	/**
	 * 当前事务关联的数据库连接
	 *
	 * @var ZcDbConnection
	 */
	private $connection;

	/**
	 * 当前事务的属性定义
	 *
	 * @var ZcTransactionDefinition
	 */
	private $transactionDefinition;

	/**
	 * 是否新的事务
	 * @var boolean
	 */
	private $newTransaction;

	/**
	 * 当前savepoint的name
	 *
	 * @var string
	 */
	private $savepoint;

	/**
	 * 当前事务是否已经结束
	 *
	 * @var boolean
	 */
	private $completed;

	public function __construct($connection, $transactionDefinition, $newTransaction) {
		$this->connection = $connection;
		$this->transactionDefinition = $transactionDefinition;
		$this->newTransaction = $newTransaction;
		$this->savepoint = null;
		$this->completed = false;
	}

	/**
	 * @return ZcDbConnection
	 */
	public function getConnection() {
		return $this->connection;
	}

	public function createAndHoldSavepoint() {
		$this->savepoint = $this->connection->createSavepoint();
	}

	public function rollbackToHeldSavepoint() {
		$this->connection->rollbackToSavepoint($this->savepoint);
		$this->savepoint = null;
	}

	public function releaseHeldSavepoint() {
		$this->connection->releaseSavepoint($this->savepoint);
		$this->savepoint = null;
	}

	public function hasSavepoint() {
		return ($this->savepoint != null);
	}

	public function hasTransaction() {
		return $this->connection->isTransactionActive();
	}

	public function isNewTransaction() {
		return $this->hasTransaction() && $this->newTransaction;
	}

	public function setCompleted() {
		$this->completed = true;
	}

	public function isCompleted() {
		return $this->completed;
	}
}