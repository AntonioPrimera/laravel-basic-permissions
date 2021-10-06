<?php

namespace AntonioPrimera\ConfigPermissions;

class Role
{
	protected $config = [];
	protected $isSuperAdmin = false;
	protected $inheritedRoles = [];
	protected $permissions = [];
	protected $permissionSeparator = ':';
	
	protected $name;
	protected $parentRole;
	
	protected $inheritedRolesAreSetUp = false;
	
	public function __construct(?string $name)
	{
		$this->name = $name ?: 'UNDEFINED';
		
		$this->config = config("permissions.roles.{$name}");
		$this->isSuperAdmin = $this->getConfigPermissions() === '*';
		$this->permissionSeparator = config("permissions.permissionSeparator", ':');
	}
	
	//=== Public methods ==============================================================================================
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	public function getName()
	{
		return $this->name;
	}
	
	public function isSuperAdmin() : bool
	{
		return $this->isSuperAdmin;
	}
	
	//--- Dealing with permissions ------------------------------------------------------------------------------------
	
	public function hasPermission(string $permission) : bool
	{
		return $this->isSuperAdmin || $this->hasOwnPermission($permission) || $this->inheritsPermission($permission);
	}
	
	//=== Protected methods ===========================================================================================
	
	//--- Checking permissions ----------------------------------------------------------------------------------------
	
	protected function hasOwnPermission(string $permission) : bool
	{
		return $this->permissionSetContainsPermission($this->getConfigPermissions(), $permission);
	}
	
	protected function permissionSetContainsPermission(array | string $permissionList, string $permission) : bool
	{
		//split the permission into components (e.g. 'store:create' => ['store', 'create'])
		$permissionComponents = array_filter(explode($this->permissionSeparator, $permission));
		if (!$permissionComponents)
			return false;
		
		//the only acceptable string is '*' - all permissions
		if (is_string($permissionList))
			return $permissionList === '*';
		
		$firstComponent = array_shift($permissionComponents);
		
		//if no more permission components are left, just search it in the given permission list
		if (!$permissionComponents)
			return $firstComponent && in_array($firstComponent, $permissionList);
		
		return array_key_exists($firstComponent, $permissionList)
			? $this->permissionSetContainsPermission(
				$permissionList[$firstComponent],
				implode($this->permissionSeparator, $permissionComponents)
			)
			: false;
	}
	
	protected function inheritsPermission($permission) : bool
	{
		if (!$this->inheritedRolesAreSetUp)
			$this->lazySetupInheritedRoles();
		
		foreach ($this->inheritedRoles as $inheritedRole) {
			if ($inheritedRole->hasPermission($permission))
				return true;
		}
		
		return false;
	}
	
	protected function getConfigPermissions() : array | string
	{
		return $this->config['permissions'] ?? [];
	}
	
	protected function getConfigInheritedRoleNames() : array
	{
		if (is_array($this->config['roles'] ?? null))
			return $this->config['roles'];
		
		if (is_array($this->config['inherits'] ?? null))
			return $this->config['inherits'];
		
		return [];
	}
	
	//--- Setup -------------------------------------------------------------------------------------------------------
	
	protected function lazySetupInheritedRoles()
	{
		//set the flag, so that we don't attempt to do this setup a second time
		$this->inheritedRolesAreSetUp = true;
		
		//don't set up any inherited roles if this is a circularly inherited role
		if ($this->isCircularInheritance())
			return;
		
		foreach ($this->getConfigInheritedRoleNames() as $inheritedRoleName) {
			$this->inheritedRoles[$inheritedRoleName] = new Role($inheritedRoleName);
			$this->inheritedRoles[$inheritedRoleName]->parentRole = $this;
		}
	}
	
	protected function isCircularInheritance() : bool
	{
		$parentRole = $this->parentRole;
		
		while ($parentRole) {
			if ($parentRole->name === $this->name)
				return true;
			
			$parentRole = $parentRole->parentRole;
		}
		
		return false;
	}
}