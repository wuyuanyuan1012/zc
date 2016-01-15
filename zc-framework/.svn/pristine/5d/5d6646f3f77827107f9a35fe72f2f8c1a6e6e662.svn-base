<?php

class ZcTransactionDefinition {
	// 以下事务的常量，参考Spring的事务框架。其中对于事务传播性，当前只支持两种

	const ISOLATION_READ_UNCOMMITTED = 1;
	const ISOLATION_READ_COMMITTED   = 2;
	const ISOLATION_REPEATABLE_READ  = 4;
	const ISOLATION_SERIALIZABLE     = 8;

	const PROPAGATION_REQUIRED = 1;
	const PROPAGATION_NESTED = 2;

	private $propagationBehavior = self::PROPAGATION_REQUIRED;
	private $isolationLevel = self::ISOLATION_READ_COMMITTED;

	public function __construct($propagationBehavior = self::PROPAGATION_REQUIRED, $isolationLevel = self::ISOLATION_READ_UNCOMMITTED) {
		$this->propagationBehavior = !empty($propagationBehavior) ? $propagationBehavior : self::PROPAGATION_REQUIRED;
		$this->isolationLevel = !empty($isolationLevel) ? $isolationLevel : self::ISOLATION_READ_COMMITTED;
	}

	public function getPropagationBehavior() {
		return $this->propagationBehavior;
	}

	public function getIsolationLevel() {
		return $this->isolationLevel;
	}
}