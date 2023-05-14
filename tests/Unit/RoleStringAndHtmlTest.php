<?php

namespace AntonioPrimera\BasicPermissions\Tests\Unit;

use AntonioPrimera\BasicPermissions\Role;
use Orchestra\Testbench\TestCase;

class RoleStringAndHtmlTest extends TestCase
{
	
	/** @test */
	public function it_will_return_the_role_label_when_cast_to_string()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'label' => 'Role A',
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		
		$this->assertEquals('Role A', (string) $role);
	}
	
	/** @test */
	public function it_will_return_an_empty_string_when_it_is_a_null_role()
	{
		$role = new Role(null);
		
		$this->assertEquals('', (string) $role);
	}
	
	/** @test */
	public function it_will_return_the_role_label_when_cast_to_html()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'label' => 'Role A',
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		
		$this->assertEquals('Role A', $role->toHtml());
	}
	
	/** @test */
	public function it_will_return_an_empty_string_when_it_is_a_null_role_and_cast_to_html()
	{
		$role = new Role(null);
		
		$this->assertEquals('', $role->toHtml());
	}
}