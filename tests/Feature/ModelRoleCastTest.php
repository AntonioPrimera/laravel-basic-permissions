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
		
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$user->role = new Role('new-role');
		$user->save();
		
		$freshUser = TestUser::find($user->id);
		$this->assertEquals('new-role', $freshUser->role);
	}
}