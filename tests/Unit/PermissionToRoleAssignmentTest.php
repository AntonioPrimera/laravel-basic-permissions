<?php

namespace AntonioPrimera\ConfigPermissions\Tests\Unit;

use AntonioPrimera\ConfigPermissions\Role;
use Orchestra\Testbench\TestCase;

class PermissionToRoleAssignmentTest extends TestCase
{
	
	/** @test */
	public function a_role_can_check_for_a_simple_permission()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'canManageStore',
							'can_Manage_Items',
						],
					]
				]
			]
		]);
		
		$role = new Role('storeManager');
		$this->assertEquals('storeManager', $role->getName());
		
		$this->assertTrue($role->hasPermission('canManageStore'));
		$this->assertTrue($role->hasPermission('can_Manage_Items'));
		$this->assertFalse($role->hasPermission('canManageUsers'));
	}
	
	/** @test */
	public function a_role_can_check_for_a_deep_permission_in_its_own_permission_set()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage'
							],
							'items' => [
								'create',
								'update',
							],
						],
					]
				]
			]
		]);
		
		$role = new Role('storeManager');
		
		$this->assertTrue($role->hasPermission('store:manage'));
		$this->assertTrue($role->hasPermission('items:create'));
		$this->assertFalse($role->hasPermission('items:delete'));
		$this->assertFalse($role->hasPermission('customers:manage'));
		$this->assertFalse($role->hasPermission('customers'));
		$this->assertFalse($role->hasPermission('store'));
		$this->assertFalse($role->hasPermission('store.manage'));
		$this->assertFalse($role->hasPermission(''));
		$this->assertFalse($role->hasPermission('*'));
	}
	
	/** @test */
	public function super_admins_have_any_permission()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => '*',
					]
				]
			]
		]);
		
		$role = new Role('storeManager');
		
		$this->assertTrue($role->hasPermission('store:manage'));
		$this->assertTrue($role->hasPermission('items:create'));
		$this->assertTrue($role->hasPermission('super:admin:has:any:permission'));
	}
	
	/** @test */
	public function roles_can_inherit_permissions_from_other_roles()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage'
							],
							'items' => [
								'manage'
							],
						],
						'roles' => [
							'storeClerk',
						]
					],
					
					'storeClerk' => [
						'permissions' => [
							'items' => [
								'create',
								'update',
							]
						],
						
						'inherits' => [
							'storeHelper'
						]
					],
					
					'storeHelper' => [
						'permissions' => [
							'items' => [
								'see',
							],
							'customers' => [
								'talkTo',
								'filter',
							]
						],
						'roles' => [],
					],
				]
			]
		]);
		
		$storeManager = new Role('storeManager');
		
		$this->assertTrue($storeManager->hasPermission('store:manage'));		//own permission
		$this->assertTrue($storeManager->hasPermission('items:create'));		//inherited from storeClerk
		$this->assertTrue($storeManager->hasPermission('items:update'));		//inherited from storeClerk
		$this->assertTrue($storeManager->hasPermission('customers:talkTo'));	//inherited from storeHelper
		$this->assertTrue($storeManager->hasPermission('customers:filter'));	//inherited from storeHelper
		$this->assertFalse($storeManager->hasPermission('store:setOnFire'));
		
		$storeClerk = new Role('storeClerk');
		
		$this->assertTrue($storeClerk->hasPermission('customers:talkTo'));	//inherited from storeHelper
		$this->assertTrue($storeClerk->hasPermission('customers:filter'));	//inherited from storeHelper
		$this->assertTrue($storeClerk->hasPermission('items:see'));			//inherited from storeHelper
		$this->assertFalse($storeClerk->hasPermission('store:manage'));
	}
	
	/** @test */
	public function multiple_levels_of_permissions_are_allowed()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage' => [
									'items' => [
										'create',
										'update',
										'description' => [
											'update'
										]
									]
								]
							],
							'items' => [
								'create',
							],
						],
					]
				]
			]
		]);
		
		$role = new Role('storeManager');
		
		$this->assertTrue($role->hasPermission('store:manage:items:create'));
		$this->assertTrue($role->hasPermission('store:manage:items:description:update'));
		
		$this->assertFalse($role->hasPermission('store:manage:items:description'));
		$this->assertFalse($role->hasPermission('store:manage:items:description:update:something'));
		$this->assertTrue($role->hasPermission('items:create'));
		$this->assertFalse($role->hasPermission('items:create:something'));
	}
	
	/** @test */
	public function wildcard_permissions_can_be_attributed_to_permission_scopes()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage'
							],
							
							'items' => '*',
						],
						'roles' => [
							'storeClerk',
						]
					],
					'storeClerk' => [
						'permissions' => [
							'customers' => '*',
						],
					]
				],
			]
		]);
		
		$role = new Role('storeManager');
		
		$this->assertTrue($role->hasPermission('store:manage'));
		
		$this->assertTrue($role->hasPermission('items:create'));
		$this->assertTrue($role->hasPermission('items:delete'));
		$this->assertTrue($role->hasPermission('items:update:description:update'));
		
		$this->assertTrue($role->hasPermission('customers:create'));		//wildcard inherited
		$this->assertTrue($role->hasPermission('customers:do:anything'));	//wildcard inherited
		
		$this->assertFalse($role->hasPermission('items'));
	}
	
	//--- Empty or Non-Existing Roles ---------------------------------------------------------------------------------
	
	/** @test */
	public function an_empty_role_will_instantiate_correctly_but_will_not_have_any_permissions()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
					],
					
					'storeClerk' => [
						'permissions' => [
						],
					],
				]
			]
		]);
		
		$storeManager = new Role('storeManager');
		$this->assertFalse($storeManager->hasPermission('store:manage'));
		
		$storeClerk = new Role('storeClerk');
		$this->assertFalse($storeClerk->hasPermission('store:manage'));
	}
	
	/** @test */
	public function creating_a_role_for_a_non_existing_role_configuration_will_just_create_a_role_with_no_permissions()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage'
							],
							
							'items' => '*',
						],
					],
				],
			]
		]);
		
		$role = new Role('storeCustomer');
		
		$this->assertFalse($role->hasPermission('store:manage'));
		$this->assertFalse($role->hasPermission('items:create'));
		$this->assertFalse($role->hasPermission('any_other_permission'));
	}
	
	/** @test */
	public function creating_a_role_with_a_null_name_will_just_create_a_role_with_no_permissions()
	{
		$role = new Role(null);
		$this->assertEquals('UNDEFINED', $role->getName());
		
		$this->assertFalse($role->hasPermission('store:manage'));
		$this->assertFalse($role->hasPermission('items:create'));
		$this->assertFalse($role->hasPermission('any_other_permission'));
	}
	
	//--- Circular inheritance ----------------------------------------------------------------------------------------
	
	/** @test */
	public function immediate_circular_inheritance_will_not_break_a_role()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage'
							],
							'items' => [
								'manage'
							],
						],
						'roles' => [
							'storeClerk',		//valid inheritance
							'storeManager',		//immediate circular inheritance
						]
					],
					
					'storeClerk' => [
						'permissions' => [
							'items' => [
								'create',
								'update',
							],
						],
					],
				],
			],
		]);
		
		$storeManager = new Role('storeManager');
		
		$this->assertTrue($storeManager->hasPermission('store:manage'));		//own permission
		$this->assertTrue($storeManager->hasPermission('items:manage'));		//own permission
		$this->assertTrue($storeManager->hasPermission('items:create'));		//inherited from storeClerk
		$this->assertFalse($storeManager->hasPermission('store:setOnFire'));
	}
	
	/** @test */
	public function deep_circular_inheritance_will_not_break_a_role()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeManager' => [
						'permissions' => [
							'store' => [
								'manage'
							],
							'items' => [
								'manage'
							],
						],
						'roles' => [
							'storeClerk',		//valid inheritance
						]
					],
					
					'storeClerk' => [
						'permissions' => [
							'items' => [
								'create',
								'update',
							]
						],
					
						'inherits' => [
							'storeOwner',		//valid inheritance
						]
					],
					
					'storeOwner' => [
						'permissions' => [
							'items' => [
								'see',
							],
							'customers' => [
								'talkTo',
								'filter',
							]
						],
						'roles' => [
							'storeManager'		//circular inheritance
						],
					],
				]
			]
		]);
		
		$storeManager = new Role('storeManager');
		
		$this->assertTrue($storeManager->hasPermission('store:manage'));		//own permission
		$this->assertTrue($storeManager->hasPermission('items:create'));		//inherited from storeClerk
		$this->assertTrue($storeManager->hasPermission('items:update'));		//inherited from storeClerk
		$this->assertTrue($storeManager->hasPermission('customers:talkTo'));	//inherited from storeOwner
		$this->assertTrue($storeManager->hasPermission('customers:filter'));	//inherited from storeOwner
		$this->assertFalse($storeManager->hasPermission('store:setOnFire'));
		
		$storeOwner = new Role('storeOwner');
		
		$this->assertTrue($storeOwner->hasPermission('customers:talkTo'));	//direct permission
		$this->assertTrue($storeOwner->hasPermission('store:manage'));		//inherited from storeManager
		$this->assertTrue($storeOwner->hasPermission('items:update'));		//inherited from storeClerk
		$this->assertFalse($storeOwner->hasPermission('items:throwAway'));
	}
}