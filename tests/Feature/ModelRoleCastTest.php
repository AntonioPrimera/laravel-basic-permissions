<?php
namespace AntonioPrimera\BasicPermissions\Tests\Feature;

use AntonioPrimera\BasicPermissions\ActorInterface;
use AntonioPrimera\BasicPermissions\Role;
use AntonioPrimera\BasicPermissions\RoleCast;
use AntonioPrimera\BasicPermissions\Tests\TestCase;
use AntonioPrimera\BasicPermissions\Tests\TestContext\Models\TestUser;

class ModelRoleCastTest extends TestCase
{
	/** @test */
	public function an_actor_can_cast_a_string_role_attribute_to_a_role_model()
	{
		$user = TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'guest'
		]);
		/* @var TestUser $user */
		
		$this->assertIsString($user->role);
		$user->mergeCasts([
			'role' => RoleCast::class,
		]);
		
		$this->assertInstanceOf(Role::class, $user->role);
		$this->assertEquals('guest', $user->role->getName());
	}
	
	/** @test */
	public function an_updated_casted_role_will_be_correctly_serialised()
	{
		$user = TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'guest'
		]);
		/* @var TestUser $user */
		
		$user->mergeCasts([
			'role' => RoleCast::class,
		]);
		
		$this->assertInstanceOf(Role::class, $user->role);
		$this->assertEquals('guest', $user->role->getName());
		
		$user->role = new Role('new-role');
		$user->save();
		
		$freshUser = TestUser::find($user->id);
		$this->assertEquals('new-role', $freshUser->role);
	}
	
	/** @test */
	public function it_can_cast_a_null_role_to_a_role_instance()
	{
		$user = TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> null,
		]);
		/* @var TestUser $user */
		
		$user->mergeCasts([
			'role' => RoleCast::class,
		]);
		
		$this->assertNull($user->fresh()->getRawOriginal('role'));
		$this->assertInstanceOf(Role::class, $user->role);
		$this->assertNull($user->role->getName());
		$this->assertTrue($user->role->isEmpty());
	}
	
	/** @test */
	public function it_can_update_a_user_role_with_null()
	{
		$user = TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'guest',
		]);
		/* @var TestUser $user */
		
		$user->mergeCasts([
			'role' => RoleCast::class,
		]);
		
		$this->assertInstanceOf(Role::class, $user->role);
		$this->assertEquals('guest', $user->role->getName());
		$this->assertFalse($user->role->isEmpty());
		
		//change it to null using 'setRole()'
		$user->setRole(null);
		$this->assertNull($user->fresh()->getRawOriginal('role'));
		
		//change it back
		$user->setRole(new Role('guest'));
		$this->assertEquals('guest', $user->fresh()->getRawOriginal('role'));
		
		//change it to null by direct assignment
		$user->role = null;
		$user->save();
		$this->assertNull($user->fresh()->getRawOriginal('role'));
	}
}