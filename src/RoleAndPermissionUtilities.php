<?php

namespace AntonioPrimera\BasicPermissions;

use Illuminate\Support\Arr;

trait RoleAndPermissionUtilities
{
	//const ROLES_CONFIG_FILE = 'permissions';
	//const ROLE_LIST_CONFIG = 'permissions.roles';
	
	/**
	 * Cache for the permissionSeparator - it brings a speed improvement of ~40%
	 * probably because it is read very often by the config function
	 * (from 119 ms down to 86 ms for 1000 permission checks)
	 */
	protected ?string $permissionSeparator = null;
	
	/**
	 * Searches recursively for the entire list of inherited roles
	 * by a given role, ignoring circular references.
	 */
	protected function determineInheritedRolesList(string|null $roleName, array &$visitedRoleNames = []): array
	{
		if (!$roleName)
			return [];
		
		$inheritedRoleNames = $this->getAssignedRoles($roleName);
		$roleNamesToCheck = array_diff($inheritedRoleNames, $visitedRoleNames);
		$visitedRoleNames = array_unique(array_merge($visitedRoleNames, $roleNamesToCheck));
		
		foreach ($roleNamesToCheck as $inheritedRoleName) {
			$inheritedRoleNames = array_unique(
				array_merge($inheritedRoleNames, $this->determineInheritedRolesList($inheritedRoleName, $visitedRoleNames))
			);
		}
		
		return $inheritedRoleNames;
	}
	
	/**
	 * Given a list of role names, return an associative array
	 * of [roleName => RoleInstance] pairs.
	 */
	protected function getRoleInstances(array|string|null $roleNames): array
	{
		$roles = [];
		
		foreach (Arr::wrap($roleNames) as $roleName) {
			$roles[$roleName] = new Role($roleName);
		}
		
		return $roles;
	}
	
	//--- Generic Config Helpers --------------------------------------------------------------------------------------
	
	/**
	 * Get the config of a given role
	 */
	protected function getRoleConfig(?string $roleName, ?string $key = null)
	{
		return $roleName
			? config("permissions.roles.$roleName" . ($key ? ".$key" : ''), [])
			: [];
	}
	
	/**
	 * Get the permission / action separator from the config.
	 */
	protected function getPermissionSeparator()
	{
		return $this->permissionSeparator
			?: ($this->permissionSeparator = config("permissions.permissionSeparator", ':'));
	}
	
	/**
	 * Get the list of roles which are directly inherited by a given role
	 */
	protected function getAssignedRoles(?string $roleName): array
	{
		return $roleName ? $this->getRoleConfigAssignedRoles($this->getRoleConfig($roleName)) : [];
	}
	
	/**
	 * Get the list of assigned permissions for a given role. This method should
	 * return either '*' (as a string) or an array of permissions. The
	 * array can be indexed or a deep associative array.
	 */
	protected function getAssignedPermissions(?string $roleName): array|string
	{
		return $roleName ? $this->getRoleConfigAssignedPermissions($this->getRoleConfig($roleName)) : [];
	}
	
	/**
	 * Retrieve and normalize the list of permissions
	 * assigned to a role, given its role config.
	 */
	protected function getRoleConfigAssignedPermissions(array $roleConfig): array|string
	{
		$permissions = $roleConfig['permissions'] ?? $roleConfig['actions'] ?? [];
		
		return $permissions === '*'
			? '*'
			: Arr::wrap($permissions);
	}
	
	/**
	 * Retrieve and normalize the list of roles directly
	 * assigned to a role, given its role config.
	 */
	protected function getRoleConfigAssignedRoles(array $roleConfig): array
	{
		return array_unique(Arr::wrap($roleConfig['roles'] ?? $roleConfig['inherits'] ?? []));
	}
}