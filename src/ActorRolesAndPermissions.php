<?php
namespace AntonioPrimera\BasicPermissions;

trait ActorRolesAndPermissions
{
	protected ?Role $roleInstance = null;
	
	//--- ActorInterface method implementations -----------------------------------------------------------------------
	
	public function getRole() : Role
	{
		if (!$this->roleInstance)
			$this->roleInstance = new Role($this->role ?? null);
			
		return $this->roleInstance;
	}
	
	public function setRole(string $role)
	{
		$this->role = $role;
		$this->save();
		
		return $this;
	}
	
	public function hasPermission(string $permission) : bool
	{
		return $this->getRole()->hasPermission($permission);
	}
	
	public function hasAllPermissions(iterable $permissions) : bool
	{
		foreach ($permissions as $permission) {
			if (!$this->hasPermission($permission))
				return false;
		}
		
		return true;
	}
	
	public function hasAnyPermission(iterable $permissions) : bool
	{
		foreach ($permissions as $permission) {
			if ($this->hasPermission($permission))
				return true;
		}
		
		return false;
	}
}