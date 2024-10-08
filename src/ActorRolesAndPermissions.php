<?php
namespace AntonioPrimera\BasicPermissions;

/**
 * @property Role|null|string $role
 */
trait ActorRolesAndPermissions
{
	protected ?Role $roleInstance = null;
	protected array $transientPermissions = [];
	
	//--- ActorInterface method implementations -----------------------------------------------------------------------
	
	public function getRole() : Role
	{
		//if the role is cast, use it
		if ($this->role instanceof Role)
			return $this->role;
		
		//if no custom attribute role cast is used or if role is null, use a cached role instance
		//an attribute with a null value will not be cast, so a cached role instance is welcome
		return $this->roleInstance ?: ($this->roleInstance = new Role($this->role));
	}
	
	public function setRole(Role|string|null $role): static
	{
		//update for cast role attribute
		if ($this->hasCast('role')) {
			$this->role = Role::instance($role);
			$this->save();
			return $this;
		}
		
		//update the role attribute (no custom attribute cast)
		$this->role = Role::name($role);
		$this->roleInstance = Role::instance($role);	//update the cached role
		$this->save();
		
		return $this;
	}
	
	public function hasPermission(string $permission) : bool
	{
		return $this->getRole()->hasPermission($permission) || $this->hasTemporaryPermission($permission);
	}
	
	public function hasAllPermissions(...$permissions) : bool
	{
		if (empty($permissions))
			return true;
		
		//if the first argument is an array, use it as the permissions list
		if (is_iterable($permissions[0]))
			$permissions = $permissions[0];
			
		foreach ($permissions as $permission) {
			if (!$this->hasPermission($permission))
				return false;
		}
		
		return true;
	}
	
	public function hasAnyPermission(...$permissions) : bool
	{
		if (empty($permissions))
			return false;
		
		//if the first argument is an array, use it as the permissions list
		if (is_iterable($permissions[0]))
			$permissions = $permissions[0];
		
		foreach ($permissions as $permission) {
			if ($this->hasPermission($permission))
				return true;
		}
		
		return false;
	}
	
	//--- Syntactic sugar ---------------------------------------------------------------------------------------------
	
	public function isSuperAdmin(): bool
	{
		return $this->getRole()->isSuperAdmin();
	}
	
	public function hasRole(Role|string $role): bool
	{
		$currentRole = $this->role instanceof Role ? $this->role->getName() : $this->role;
		$roleName = $role instanceof Role ? $role->getName() : $role;
		return $currentRole === $roleName;
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