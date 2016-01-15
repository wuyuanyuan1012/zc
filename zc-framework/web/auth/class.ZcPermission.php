<?php
class ZcPermission {
	private $zcPermissionId;
	private $name;
	private $desc;

	public function __construct($zcPermissionId, $name, $desc) {
		$this->zcPermissionId = $zcPermissionId;
		$this->name = $name;
		$this->desc = $desc;
	}

	public function getZcPermissionId() {
		return $this->zcPermissionId;
	}

	public function setZcPermissionId($zcPermissionId) {
		$this->zcPermissionId = $zcPermissionId;
		return $this;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	public function getDesc() {
		return $this->desc;
	}

	public function setDesc($desc) {
		$this->desc = $desc;
		return $this;
	}
}