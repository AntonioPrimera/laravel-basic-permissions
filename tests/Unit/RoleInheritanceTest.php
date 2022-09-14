<?php
namespace AntonioPrimera\BasicPermissions\Tests\Unit;

use AntonioPrimera\BasicPermissions\Role;
use AntonioPrimera\BasicPermissions\Tests\TestContext\Traits\TestContexts;
use Orchestra\Testbench\TestCase;

class RoleInheritanceTest extends TestCase
{
	use TestContexts;
	
	/** @test */
	public function it_can_provide_a_list_of_directly_inherited_roles()
	{
		$this->setupContextComplexRoleSet();
		
		$role = new Role('roleA');
		$roles = $role->getInheritedRoles(false);
		
		$this->assertIsArray($roles);
		$this->assertCount(4, $roles);
		$this->assertEmpty(array_diff(['roleB', 'roleC', 'roleD', 'roleE'], ['roleB', 'roleC', 'roleD', 'roleE']));
	}
	
	/** @test */
	public function it_can_provide_a_list_of_deep_inherited_roles_ignoring_circular_inheritance()
	{
		$this->setupContextComplexRoleSet();
		
		$role = new Role('roleD');
		$roles = $role->getInheritedRoles(true);
		
		$this->assertIsArray($roles);
		$this->assertCount(13, $roles);
		$this->assertEmpty(
			array_diff(
				['roleH', 'roleI', 'roleJ', 'roleL', 'roleM', 'roleK', 'roleD', 'roleB', 'roleX', 'roleF', 'roleG', 'roleE', 'roleY'],
				['roleH', 'roleI', 'roleJ', 'roleL', 'roleM', 'roleK', 'roleD', 'roleB', 'roleX', 'roleF', 'roleG', 'roleE', 'roleY'],
			)
		);
	}
	
	/** @test */
	public function it_can_check_if_a_role_is_inherited_deep()
	{
		$this->setupContextComplexRoleSet();
		
		$role = new Role('roleD');
		$this->assertTrue($role->inheritsRole('roleK'));
		$this->assertFalse($role->inheritsRole('roleC'));
	}
	
	/** @test */
	public function it_can_check_for_inherited_permissions()
	{
		$this->setupContextComplexRoleSet();
		
		$role = new Role('roleA');
		
		$allowedPermissions = [
			'A1:c', 'A1:r', 'A1:u', 'A1:d',
			'A2:c:s', 'A2:c:m',
			'A2:r:s', 'A2:r:m',
			'A2:u:s', 'A2:d:x',
			'A3:anything', 'A3:c:s',
			'action-1', 'action-2',
			'L1:c', 'L1:r',
			'L2:c:s', 'L2:d:x',
			'L3:anything', 'L3:c:s:x',
			'L4-c', 'L4-r',
		];
		
		$notAllowedPermissions = [
			'A1', 'A1:x', 'A1:c:s', 'A2:c:r', 'action-1:c', 'action-3',
			'L1:x', 'L2:c:x',' L4-x', 'L4-c:s', 'L4:c'
		];
		
		foreach ($allowedPermissions as $permission) {
			$this->assertTrue($role->hasPermission($permission), "Role does not allow permission $permission");
		}
		
		foreach ($notAllowedPermissions as $permission) {
			$this->assertFalse($role->hasPermission($permission), "Role allows permission $permission");
		}
	}
}