<?php

class ZcDbRbac extends ZcRbac {
	private $db;

	public function __construct() {
		$this->db = Zc::getDb();
	}

	private function updateRoleOnly(ZcRole $role) {
		$this->db->update('zc_role', array (
				'name' => $role->getName()
		), 'zc_role_id = %i AND name <> %s', $role->getZcRoleId(), $role->getName());

		$this->db->update('zc_role', array (
				'desc' => $role->getDesc(),
				'date_modified' => new ZcDbEval('now()')
		), 'zc_role_id = %i', $role->getZcRoleId());
	}

	private function addRoleOnly(ZcRole $role) {
		$this->db->insert('zc_role', array (
				'name' => $role->getName(),
				'desc' => $role->getDesc(),
				'date_added' => new ZcDbEval('now()'),
				'date_modified' => new ZcDbEval('now()')
		));
		return $this->db->lastInsertId();
	}

	public function checkPartialOrder($roleId = null, $subRoleIds = array()) {
		$nodes = $this->db->queryFirstColumn("select zc_role_id from zc_role order by zc_role_id desc");

		$where = ($roleId > 0 ? (' where zc_role_id <> ' . $roleId) : '');
		$edges = $this->db->queryAllLists("select zc_role_id, zc_sub_role_id from zc_role_hierarchy" . $where);
		if ($roleId > 0 && is_array($subRoleIds) && !empty($subRoleIds)) {
			foreach ($subRoleIds as $subRoleId) {
				$edges[] = array (
						$roleId,
						$subRoleId
				);
			}
		}

		// 用链地址法初始化整个有向图和入度数组
		$graph = array ();
		$inDegree = array ();
		foreach ($nodes as $node) {
			$graph[(int)$node] = array ();
			$inDegree[(int)$node] = 0;
		}
		foreach ($edges as $edge) {
			$src = (int)$edge[0];
			$dst = (int)$edge[1];
			$graph[$src][$dst] = $dst;
			$inDegree[$dst] ++;
		}

		// 建立入度为0的结点栈
		$zeroStack = array ();
		foreach ($inDegree as $key => $value) {
			if ($value == 0) {
				$zeroStack[] = $key;
			}
		}

		$count = 0;
		while (!empty($zeroStack)) {
			$node = array_pop($zeroStack);
			$count ++;
			foreach ($graph[$node] as $dst) {
				$inDegree[$dst] --;
				if ($inDegree[$dst] === 0) {
					$zeroStack[] = $dst;
				}
			}
		}

		return ($count === count($nodes));
	}

	private function addRoleHierarchy($roleId, $subRoleIds) {
		$checkPartialOrder = $this->checkPartialOrder($roleId, $subRoleIds);
		if (!$checkPartialOrder) {
			throw new ZcRolePartialOrderException('checkPartialOrder failed');
		}

		$this->db->delete('zc_role_hierarchy', 'zc_role_id = %i', $roleId);

		if (!empty($subRoleIds)) {
			$roleHierarchy = array ();
			foreach ($subRoleIds as $subRoleId) {
				$roleHierarchy[] = array (
						'zc_role_id' => $roleId,
						'zc_sub_role_id' => $subRoleId,
						'date_added' => new ZcDbEval('now()')
				);
			}
			$this->db->insert('zc_role_hierarchy', $roleHierarchy);
		}
	}

	private function addRoleToPermissions($roleId, $permissionIds) {
		$this->db->delete('zc_role_to_permission', 'zc_role_id = %i', $roleId);

		if (!empty($permissionIds)) {
			$roleToPermissions = array ();
			foreach ($permissionIds as $permissionId) {
				$roleToPermissions[] = array (
						'zc_role_id' => $roleId,
						'zc_permission_id' => $permissionId,
						'date_added' => new ZcDbEval('now()')
				);
			}
			$this->db->insert('zc_role_to_permission', $roleToPermissions);
		}
	}

	private function addOrUpdateRole(ZcRole $role) {
		$transStatus = $this->db->startTransaction();
		$oldErrorMode = $this->db->setErrorMode(ZcDB::ERROR_MODE_EXCEPTION);

		try {
			$roleId = $role->getZcRoleId();
			if ($roleId > 0) {
				$this->updateRoleOnly($role);
			} else {
				$roleId = $this->addRoleOnly($role);
			}

			$this->addRoleHierarchy($roleId, $role->getSubRoleIds());

			$this->addRoleToPermissions($roleId, $role->getPermissionIds());

			$this->db->commit($transStatus);
			$this->db->setErrorMode($oldErrorMode);
			return $roleId;
		} catch (Exception $ex) {
			$this->db->rollback($transStatus);
			$this->db->setErrorMode($oldErrorMode);
			return false;
		}
	}

	public function addRole(ZcRole $role) {
		$role->setZcRoleId(null);
		return $this->addOrUpdateRole($role);
	}

	public function updateRole(ZcRole $role) {
		return $this->addOrUpdateRole($role);
	}

	public function deleteRoles($roleIds) {
		if (empty($roleIds)) {
			return 0;
		}
		if (is_int($roleIds)) {
			$tmp[] = $roleIds;
			$roleIds = $tmp;
		}

		$transStatus = $this->db->startTransaction();
		$oldErrorMode = $this->db->setErrorMode(ZcDB::ERROR_MODE_EXCEPTION);

		try {
			$this->db->delete('zc_role_hierarchy', 'zc_role_id in %li or zc_sub_role_id in %li', $roleIds, $roleIds);
			$this->db->delete('zc_role_to_permission', 'zc_role_id in %li', $roleIds);
			$this->db->delete('zc_user_to_role', 'zc_role_id in %li', $roleIds);
			$this->db->delete('zc_role', 'zc_role_id in %li', $roleIds);

			$this->db->commit($transStatus);
			$this->db->setErrorMode($oldErrorMode);
		} catch (Exception $ex) {
			$this->db->rollback($transStatus);
			$this->db->setErrorMode($oldErrorMode);
			return false;
		}
	}

	public function getAllRole($userId = null) {
		$sql = '';
		if ($userId > 0) {
			$rps = $this->getUserAllRoleIdsAndPermissionIds($userId);
			$userRoleIds = $rps[0];
			$sql = $this->db->prepare("select * from zc_role where zc_role_id in %li", $userRoleIds);
		} else {
			$sql = "select * from zc_role";
		}

		$ret = array ();

		$rows = $this->db->query($sql);
		foreach ($rows as $row) {
			$ret[] = new ZcPermission($row['zc_role_id'], $row['name'], $row['desc']);
		}

		return $ret;
	}

	public function addPermission(ZcPermission $permission) {
		$ret = $this->db->insert('zc_permission', array (
				'name' => $permission->getName(),
				'desc' => $permission->getDesc(),
				'date_added' => new ZcDbEval('now()'),
				'date_modified' => new ZcDbEval('now()')
		));
		if ($ret === false) {
			return false;
		}
		return $this->db->lastInsertId();
	}

	public function updatePermission(ZcPermission $permission) {
		$transStatus = $this->db->startTransaction();
		$oldErrorMode = $this->db->setErrorMode(ZcDB::ERROR_MODE_EXCEPTION);

		try {
			$this->db->update('zc_permission', array (
					'name' => $permission->getName()
			), 'zc_permission_id = %i AND name <> %s', $permission->getZcPermissionId(), $permission->getName());

			$this->db->update('zc_permission', array (
					'desc' => $permission->getDesc(),
					'date_modified' => new ZcDbEval('now()')
			));

			$this->db->commit($transStatus);
			$this->db->setErrorMode($oldErrorMode);
		} catch (Exception $ex) {
			$this->db->rollback($transStatus);
			$this->db->setErrorMode($oldErrorMode);
			return false;
		}
	}

	public function deletePermissions($permissionIds) {
		if (empty($permissionIds)) {
			return 0;
		}
		if (is_int($permissionIds)) {
			$tmp[] = $permissionIds;
			$permissionIds = $tmp;
		}

		$transStatus = $this->db->startTransaction();
		$oldErrorMode = $this->db->setErrorMode(ZcDB::ERROR_MODE_EXCEPTION);

		try {
			$this->db->delete('zc_permission', 'zc_permission_id in %li', $permissionIds);
			$this->db->delete('zc_role_to_permission', 'zc_permission_id in %li', $permissionIds);

			$this->db->commit($transStatus);
			$this->db->setErrorMode($oldErrorMode);
		} catch (Exception $ex) {
			$this->db->rollback($transStatus);
			$this->db->setErrorMode($oldErrorMode);
			return false;
		}
	}

	public function getAllPermission($userId = null) {
		$sql = '';
		if ($userId > 0) {
			$rps = $this->getUserAllRoleIdsAndPermissionIds($userId);
			$userPermissionIds = $rps[1];
			$sql = $this->db->prepare("select * from zc_permission where zc_permission_id in %li", $userPermissionIds);
		} else {
			$sql = "select * from zc_permission";
		}

		$ret = array ();

		$rows = $this->db->query($sql);
		foreach ($rows as $row) {
			$ret[] = new ZcPermission($row['zc_permission_id'], $row['name'], $row['desc']);
		}

		return $ret;
	}

	public function assign($userId, $roleIds = array()) {
		if (empty($roleIds)) {
			return 0;
		}
		if (is_int($roleIds)) {
			$tmp[] = $roleIds;
			$roleIds = $tmp;
		}

		$userToRoleIds = array ();
		foreach ($roleIds as $roleId) {
			$userToRoleIds[] = array (
					'zc_user_id' => $userId,
					'zc_role_id' => $roleId,
					'date_added' => new ZcDbEval('now()')
			);
		}
		return $this->db->insert('zc_user_to_role', $userToRoleIds);
	}

	public function revoke($userId, $roleIds = array()) {
		if (empty($roleIds)) {
			return 0;
		}
		if (is_int($roleIds)) {
			$tmp[] = $roleIds;
			$roleIds = $tmp;
		}

		return $this->db->delete('zc_user_to_role', 'zc_user_id = %i and zc_role_id in %li', $userId, $roleIds);
	}

	private function getRole($roleId) {
		$roleRow = $this->db->queryFirstRow("select * from zc_role where zc_role_id = %i", $roleId);
		if (empty($roleRow)) {
			return null;
		}
		$subRoleIds = $this->db->queryFirstColumn("select zc_sub_role_id from zc_role_hierarchy where zc_role_id = %i", $roleId);
		$permissionIds = $this->db->queryFirstColumn("select zc_permission_id from zc_role_to_permission where zc_role_id = %i", $roleId);
		return new ZcRole($roleId, $roleRow['name'], $roleRow['desc'], $subRoleIds, $permissionIds);
	}

	/**
	 * 这个方法之所以不会陷入死循环，是因为角色是偏序关系的
	 *
	 * @param int $roleId
	 * @param int $permissionId
	 * @return boolean
	 */
	private function checkAccessRecursive($roleId, $permissionId) {
		$role = $this->getRole($roleId);
		if (!$role) {
			return false;
		}

		$permissionIds = $role->getPermissionIds();
		if (empty($permissionIds)) {
			return false;
		}
		foreach ($role->getPermissionIds() as $pid) {
			if ($pid == $permissionId) {
				return true;
			}
		}

		$subRoleIds = $role->getSubRoleIds();
		if (empty($subRoleIds)) {
			return false;
		}
		$pass = false;
		foreach ($role->getSubRoleIds() as $roleId) {
			if ($this->checkAccessRecursive($roleId, $permissionId)) {
				$pass = true;
				break;
			}
		}
		return $pass;
	}

	private function getRoleIdsAndPermissionIdsRecursive($roleId, &$allRoleIds, &$allPermissionIds) {
		$allRoleIds[] = $roleId;
		$role = $this->getRole($roleId);
		$allPermissionIds = array_merge($allPermissionIds, $role->getPermissionIds());

		$subRoleIds = $role->getSubRoleIds();
		foreach($subRoleIds as $subRoleId) {
			$this->getRoleIdsAndPermissionIdsRecursive($subRoleId, $allRoleIds, $allPermissionIds);
		}
	}

	private function getUserAllRoleIdsAndPermissionIds($userId) {
		$allRoleIds = array();
		$allPermissionIds = array();

		$directRoleIds = $this->getUserDirectRoleIds($userId);
		foreach($directRoleIds as $roleId) {
			$this->getRoleIdsAndPermissionIdsRecursive($roleId, $allRoleIds, $allPermissionIds);
		}

		return array(array_unique($allRoleIds), array_unique($allPermissionIds));
	}

	private function getUserDirectRoleIds($userId) {
		return $this->db->queryFirstColumn("select zc_role_id from zc_user_to_role where zc_user_id = %i", $userId);
	}

	public function checkAccess($userId, $permissionName, $assert = null) {
		if ($assert) {
			if ($assert instanceof ZcIAuthAssertion) {
				if (!$assert->assert($this)) {
					return false;
				}
			} elseif (is_callable($assert)) {
				if (!$assert($this)) {
					return false;
				}
			} else {
				throw new Exception('Assertions must be a Callable or an instance of ZcIAssertionInterface');
			}
		}

		$permissionId = $this->db->queryFirstField("select zc_permission_id from zc_permission where name = %s", $permissionName);
		$roleIds = $this->getUserDirectRoleIds($userId);

		$pass = false;
		foreach ($roleIds as $roleId) {
			if ($this->checkAccessRecursive($roleId, $permissionId)) {
				$pass = true;
				break;
			}
		}
		return $pass;
	}
}