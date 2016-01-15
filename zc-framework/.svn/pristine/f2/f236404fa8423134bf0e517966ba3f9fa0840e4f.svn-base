<?php

class ZcDbException extends Exception {
	private $sql;
	
	public function __construct($message, $sql = '', $code = 0) {
		parent::__construct('[' . $sql . '], ' . $message, $code);
		$this->sql = $sql;
	}
	
	public function getSql() {
		return $this->sql;
	}
}