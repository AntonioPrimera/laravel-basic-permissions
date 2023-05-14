<?php
namespace AntonioPrimera\BasicPermissions;

/**
 * Class Role
 * @package AntonioPrimera\BasicPermissions
 *
 * @property string $label
 * @property string|null $description
 */
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
	
	//=== Magic stuff =================================================================================================
	
	public function __get(string $name)
	{
		if (is_callable([$this, $method = 'get' . ucfirst($name) . 'Attribute']))
			return $this->$method();
		
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		
		return null;
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
	
	//=== Accessors ===================================================================================================
	
	protected function getLabelAttribute(): string|null
	{
		$configLabel = $this->config['label'] ?? null;
		
		//if a simple string is provided, just return it
		if (is_string($configLabel))
			return $configLabel;
		
		if (is_array($configLabel))
			return $configLabel[app()->getLocale()]									//get the label for current locale
				?? $configLabel[config('app.fallback_locale', 'en')]	//try the fallback locale
				?? $configLabel['en']												//try english as a last resort
				?? $this->name;														//if all else fails, return the name
			
		return $this->name;
	}
	
	protected function getDescriptionAttribute(): string|null
	{
		$configDescription = $this->config['description'] ?? null;
		
		//if a simple string is provided, just return it
		if (is_string($configDescription))
			return $configDescription;
		
		if (is_array($configDescription))
			return $configDescription[app()->getLocale()]					//get the description for the current locale
				?? $configDescription[config('app.fallback_locale', 'en')]	//try the fallback locale
				?? $configDescription['en']									//try english as a last resort
				?? null;													//if all else fails, return null
		
		return null;
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