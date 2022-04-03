<?php

namespace AntonioPrimera\BasicPermissions\Tests\Unit;

use AntonioPrimera\BasicPermissions\Tests\TestCase;
use AntonioPrimera\BasicPermissions\Tests\TestContext\Models\TestUser;

class TemporaryPermissionsTest extends TestCase
{
	/** @test */
	public function it_can_assign_temporary_permissions_to_an_actor()
	{
		$user = $this->createTestuser();
		
		$this->assertFalse($user->hasPermission('do-something'));
		$user->assignTemporaryPermission('do-something');
		$this->assertTrue($user->hasPermission('do-something'));
	}
	
	/** @test */
	public function temporary_permissions_are_transient_and_will_not_be_persisted_to_the_db()
	{
		$user = $this->createTestuser();
		
		$user->assignTemporaryPermission('do-something');
		$this->assertTrue($user->hasPermission('do-something'));
		
		$user->save();
		$user = TestUser::find($user->id);
		$this->assertFalse($user->hasPermission('do-something'));
	}
	
	/** @test */
	public function it_can_remove_a_temporary_permission()
	{
		$user = $this->createTestuser();
		
		$this->assertFalse($user->hasPermission('do-something'));
		$user->assignTemporaryPermission('do-something');
		$this->assertTrue($user->hasPermission('do-something'));
		$user->removeTemporaryPermission('do-something');
		$this->assertFalse($user->hasPermission('do-something'));
	}
	
	/** @test */
	public function it_can_clear_all_temporary_permissions()
	{
		$user = $this->createTestuser();
		
		$this->assertFalse($user->hasPermission('do-something'));
		$user->assignTemporaryPermission('do-something');
		$user->assignTemporaryPermission('do-something-else');
		$this->assertTrue($user->hasAllPermissions(['do-something', 'do-something-else']));
		
		$user->clearTemporaryPermissions();
		$this->assertFalse($user->hasAnyPermission(['do-something', 'do-something-else']));
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function createTestuser(): TestUser
	{
		return TestUser::create([
			'name' 		=> 'Gigi',
			'password'  => 'Migi',
			'role'		=> 'guest',
		]);
	}
}