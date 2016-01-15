<?php
/**
 * 实现RBAC，Role之间可以有层次关系，并且这个层次关系满足偏序关系
 *
 * @author tangjianhui 2014-4-7 上午1:14:18
 *
 * @link http://csrc.nist.gov/groups/SNS/rbac/documents/design_implementation/Intro_role_based_access.htm
 * @link http://csrc.nist.gov/groups/SNS/rbac/faq.html#top
 * @link http://csrc.nist.gov/groups/SNS/rbac/documents/sandhu96.pdf
 * @link http://csrc.nist.gov/groups/SNS/rbac/
 */
abstract class ZcRbac {
	abstract public function addPermission(ZcPermission $permission);
	abstract public function updatePermission(ZcPermission $permission);
	abstract public function deletePermissions($permissionIds);
	abstract public function getAllPermission($userId = null);

	abstract public function addRole(ZcRole $role);
	abstract public function updateRole(ZcRole $role);
	abstract public function deleteRoles($roleIds);
	abstract public function getAllRole($userId = null);

	abstract public function assign($userId, $roleIds = array());
	abstract public function revoke($userId, $roleIds = array());

	/**
	 * 检测偏序结构，根据RBAC1的要求，对于所有的Role，本质上是偏序结构的，是个有向无环图，检测本质上是进行一次拓扑排序
	 */
	abstract public function checkPartialOrder($roleId = null, $subRoleIds = array());

	abstract public function checkAccess($userId, $permissionName, $assert = null);
}