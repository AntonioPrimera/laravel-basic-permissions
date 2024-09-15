<?php
namespace AntonioPrimera\BasicPermissions\Tests\Feature;

use AntonioPrimera\BasicPermissions\ActorInterface;
use AntonioPrimera\BasicPermissions\Role;
use AntonioPrimera\BasicPermissions\Tests\TestCase;
use AntonioPrimera\BasicPermissions\Tests\TestContext\Models\TestUser;

class ModelInterfaceTest extends TestCase
{
	
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
		$this->assertTrue($actor->hasAllPermissions('store:manage', 'items:create'));
		$this->assertTrue($actor->hasAnyPermission(['items:update', 'items:delete', 'something:else']));
		$this->assertTrue($actor->hasAnyPermission('items:update', 'items:delete', 'something:else'));
		
		$this->assertFalse($actor->hasPermission('store:setOnFire'));
	}
}