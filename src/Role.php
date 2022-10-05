<?php
namespace AntonioPrimera\BasicPermissions;

class Role
{
	use RoleAndPermissionUtilities;
	
	//role attributes
	protected string|null $name;
	protected bool $isEmpty;	//whether this is a real role, or just an empty role with no permissions
	
	//config data
	protected mixed $config;
	protected bool $isSuperAdmin;
	
	public function __construct(?string $name)
	{
		$this->name = $name;
		$this->isEmpty = !$name;
		$this->config = $this->getRoleConfig($name);
		$this->isSuperAdmin = $this->getRoleConfigAssignedPermissions($this->config) === '*';
	}
	
	/**
	 * Normalize a Role instance or a role name to a Role instance.
	 */
	public static function instance(Role|string|null $role): static
	{
		return $role instanceof Role ? $role : new static($role);
	}
	
	/**
	 * Normalize a Role instance or a role name to a string role name.
	 */
	public static function name(Role|string|null $role): string|null
	{
		return $role instanceof Role ? $role->getName() : $role;
	}
	
	//=== Public methods ==============================================================================================
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	public function getName(): string|null
	{
		return $this->name;
	}
	
	public function isSuperAdmin() : bool
	{
		return $this->isSuperAdmin;
	}
	
	public function isEmpty(): bool
	{
		return $this->isEmpty;
	}
	
	//--- Dealing with permissions ------------------------------------------------------------------------------------
	
	public function hasPermission(string $permission) : bool
	{
		//empty roles have no permission
		if ($this->isEmpty)
			return false;
		
		//super-admins are allowed to do anything
		if ($this->isSuperAdmin)
			return true;
		
		//check if the role allows the given action itself
		if ($this->permissionSetContainsPermission($this->getRoleConfigAssignedPermissions($this->config), $permission))
			return true;
		
		//check if any of the inherited roles allow the given action
		$inheritedRoleNames = $this->determineInheritedRolesList($this->name);
		
		foreach ($inheritedRoleNames as $inheritedRoleName)
			if ($this->permissionSetContainsPermission($this->getAssignedPermissions($inheritedRoleName), $permission))
				return true;
		
		return false;
	}
	
	//--- Dealing with inherited roles --------------------------------------------------------------------------------
	
	public function getInheritedRoles(bool $deep = false): array
	{
		if ($this->isEmpty)
			return [];
		
		$roleNameList = $deep
			? $this->determineInheritedRolesList($this->name)
			: $this->getRoleConfigAssignedRoles($this->config);
		
		return $this->getRoleInstances($roleNameList);
	}
	
	public function inheritsRole(Role|string $role): bool
	{
		return !$this->isEmpty
			&& in_array(
			$role instanceof Role ? $role->name : $role,
			$this->determineInheritedRolesList($this->name)
		);
	}
	
	//=== Protected methods ===========================================================================================
	
	/**
	 * Given a list of permissions, check if a permission is included in the given list. This is the
	 * base method for checking whether a (string) permission is allowed by a permission set.
	 * The permission set can be: (string) '*', an indexed or a deep associative array.
	 *
	 * This check also covers wildcards:
	 * 		e.g. 'store:read' is allowed by permission set: '*'
	 * 			 'store:read' is allowed by permission set: ['store' => '*']
	 * 			 'store:read' is allowed by permission set: ['store' => ['read']]
	 *			 'store:read:single' is allowed by permission set: ['store' => ['read' => ['single]]]
	 */
	protected function permissionSetContainsPermission(array | string $permissionList, string $permission) : bool
	{
		$permissionSeparator = $this->getPermissionSeparator();
		
		//split the permission into components (e.g. 'store:create' => ['store', 'create'])
		$permissionComponents = array_filter(explode($permissionSeparator, $permission));
		if (!$permissionComponents)
			return false;
		
		//the only acceptable string is '*' - super-admin: allows all permissions
		if (is_string($permissionList))
			return $permissionList === '*';
		
		$firstComponent = array_shift($permissionComponents);
		
		//if the permission is a simple one (just one component), search it in the given permission list
		if (!$permissionComponents)
			return $firstComponent && in_array($firstComponent, $permissionList);
		
		//the permission is a complex one (e.g. store:create), so it must match 'store' => ['create', ...]
		return array_key_exists($firstComponent, $permissionList)
			&& $this->permissionSetContainsPermission(
				$permissionList[$firstComponent],
				implode($permissionSeparator, $permissionComponents)
			);
	}
}