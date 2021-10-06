<?php

namespace AntonioPrimera\ConfigPermissions;

trait ActorRolesAndPermissions
{
	protected $roleInstance;
	
	//--- ActorInterface method implementations -----------------------------------------------------------------------
	
	/**
	 * Get all roles belonging to the actor
	 *
	 * @return iterable
	 */
	public function getRole() : Role
	{
		if (!$this->roleInstance)
			$this->roleInstance = new Role($this->role ?? null);
			
		return $this->roleInstance;
	}
	
	/**
	 * Check if the actor has a given permission
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasPermission(string $permission) : bool
	{
		return $this->getRole()->hasPermission($permission);
	}
	
	/**
	 * Check if the actor has all permissions in a given list
	 *
	 * @param iterable $permissions
	 *
	 * @return bool
	 */
	public function hasAllPermissions(iterable $permissions) : bool
	{
		foreach ($permissions as $permission) {
			if (!$this->hasPermission($permission))
				return false;
		}
		
		return true;
	}
	
	/**
	 * Check if the actor has at least one of the given permissions
	 *
	 * @param iterable $permissions
	 *
	 * @return bool
	 */
	public function hasAnyPermission(iterable $permissions) : bool
	{
		foreach ($permissions as $permission) {
			if ($this->hasPermission($permission))
				return true;
		}
		
		return false;
	}
}