<?php
namespace AntonioPrimera\BasicPermissions\Tests\Feature;

use AntonioPrimera\BasicPermissions\ActorInterface;
use AntonioPrimera\BasicPermissions\Role;
use AntonioPrimera\BasicPermissions\Tests\TestCase;
use AntonioPrimera\BasicPermissions\Tests\TestContext\Models\TestUser;

class ModelInterfaceTest extends TestCase
{
	
	//todo: it seems that the migrations only run for the first test
	//todo: find a way to re-run migrations for each test
	
	/** @test */
	public function migrations_are_loaded_from_the_package()
	{
		$this->assertDatabaseCount('users', 0);
		TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'guest'
		]);
	
		$this->assertDatabaseCount('users', 1);
	}
	
	/** @test */
	public function permission_checks_can_be_done_on_an_actor()
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
		
		TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'storeManager'
		]);
		
		$this->assertDatabaseCount('users', 1);
		$actor = TestUser::first();
		
		$this->assertInstanceOf(ActorInterface::class, $actor);
		$this->assertInstanceOf(Role::class, $actor->getRole());
		$this->assertEquals('storeManager', $actor->role);
		$this->assertEquals('storeManager', $actor->getRole()->getName());
		
		$this->assertTrue($actor->hasPermission('store:manage'));
		$this->assertTrue($actor->hasAllPermissions(['store:manage', 'items:create']));
		$this->assertTrue($actor->hasAnyPermission(['items:update', 'items:delete', 'something:else']));
		
		$this->assertFalse($actor->hasPermission('store:setOnFire'));
	}
	
	/** @test */
	public function benchmark_permission_checks()
	{
		config([
			'permissions' => [
				'roles' => [
					'storeOwner' => [
						'permissions' => [
							'store' => [
								'own'
							],
							'items' => [
								'manage'
							],
						],
						'roles' => [
							'storeManager'
						],
					],
					
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
						'inherits' => [
							'storeSupervisor',
							'storeClerk',
							'nonExistingRole',
						],
					],
					
					'storeClerk' => [
						'permissions' => [
							'items' => [
								'arrange',
								'sell',
							],
						],
					],
					
					'storeSupervisor' => [
						'permissions' => [
							'store' => [
								'supervise'
							],
							'items' => [
								'check'
							],
						],
						
						'roles' => [
							'storeClerk'
						],
					],
				]
			]
		]);
		
		TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'storeOwner'
		]);
		
		$user = TestUser::first();
		$this->assertInstanceOf(TestUser::class, $user);
		
		$permissionsToCheck = [
			'store:own', 'store:manage', 'items:arrange', 'store:supervise', 'items:check',		//valid permissions
			'store:setOnFire', 'store:close', 'items:destroy', 'items:play', 'something:else',	//invalid permissions
		];
		
		$runs = 10000;
		$startTime = microtime(true);
		$checks = ['successful' => 0, 'unsuccessful' => 0];
		foreach (range(1, $runs) as $index)
			if ($user->getRole()->hasPermission($permissionsToCheck[random_int(0, count($permissionsToCheck) - 1)]))
				$checks['successful']++;
			else
				$checks['unsuccessful']++;
		
		echo "Time elapsed for $runs runs: " . (round((microtime(true) - $startTime) * 1000)) . " ms \n";
	}
}