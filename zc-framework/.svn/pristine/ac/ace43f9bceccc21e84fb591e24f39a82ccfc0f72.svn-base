<?php

class ZcDbConnectionException extends ZcDbException {
	private $dbId;
	
	public function __construct($message, $dbId = '', $code = 0) {
		parent::__construct($message, $code);
		$this->dbId = $dbId;
	}
	
	public function getDbId() {
		return $this->dbId;
	}
}