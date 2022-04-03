<?php
namespace AntonioPrimera\BasicPermissions;

trait ActorRolesAndPermissions
{
	protected ?Role $roleInstance = null;
	protected array $transientPermissions = [];
	
	//--- ActorInterface method implementations -----------------------------------------------------------------------
	
	public function getRole() : Role
	{
		if (!$this->roleInstance)
			$this->roleInstance = new Role($this->role ?? null);
			
		return $this->roleInstance;
	}
	
	public function setRole(string $role): static
	{
		$this->role = $role;
		$this->save();
		
		return $this;
	}
	
	public function hasPermission(string $permission) : bool
	{
		return $this->getRole()->hasPermission($permission) || $this->hasTemporaryPermission($permission);
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
	
	//--- Testing helpers ---------------------------------------------------------------------------------------------
	
	public function assignTemporaryPermission(string $permission): static
	{
		$this->transientPermissions[$permission] = true;
		return $this;
	}
	
	public function removeTemporaryPermission(string $permission): static
	{
		unset($this->transientPermissions[$permission]);
		return $this;
	}
	
	public function clearTemporaryPermissions(): static
	{
		$this->transientPermissions = [];
		return $this;
	}
	
	public function hasTemporaryPermission(string $permission): bool
	{
		return $this->transientPermissions[$permission] ?? false;
	}
}