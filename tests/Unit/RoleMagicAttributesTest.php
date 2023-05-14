<?php

namespace AntonioPrimera\BasicPermissions\Tests\Unit;

use AntonioPrimera\BasicPermissions\Role;
use Orchestra\Testbench\TestCase;

class RoleMagicAttributesTest extends TestCase
{
	
	//--- Role label --------------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_return_the_text_label_if_provided_in_the_config_as_a_string()
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
		
		$this->assertEquals('Role A', $role->label);
	}
	
	/** @test */
	public function it_can_return_the_text_label_if_provided_in_the_config_as_an_array()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'label' => [
							'en' => 'Role A - en',
							'es' => 'Role A - es',
						],
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		app()->setLocale('es');
		
		$this->assertEquals('Role A - es', $role->label);
	}
	
	/** @test */
	public function it_will_return_the_label_for_the_fallback_locale_if_no_label_was_provided_for_the_current_locale()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'label' => [
							'en' => 'Role A - en',
							'es' => 'Role A - es',
						],
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		app()->setLocale('fr');
		
		$this->assertEquals('en', config('app.fallback_locale'));
		$this->assertEquals('Role A - en', $role->label);
	}
	
	/** @test */
	public function it_will_return_the_role_name_if_no_label_was_provided_for_the_locale_or_the_fallback_locale()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'label' => [
							'de' => 'Role A - en',
							'es' => 'Role A - es',
						],
					],
				],
			],
		]);
		
		$role = new Role('roleB');
		app()->setLocale('fr');
		
		$this->assertEquals('roleB', $role->label);
	}
	
	/** @test */
	public function it_will_return_the_role_name_if_no_label_was_provided()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		
		$this->assertEquals('roleA', $role->label);
	}
	
	//--- Role description --------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_return_the_text_description_if_provided_in_the_config_as_a_string()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'description' => 'Role A description',
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		
		$this->assertEquals('Role A description', $role->description);
	}
	
	/** @test */
	public function it_can_return_the_text_description_if_provided_in_the_config_as_an_array()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'description' => [
							'en' => 'Role A description - en',
							'es' => 'Role A description - es',
						],
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		app()->setLocale('es');
		
		$this->assertEquals('Role A description - es', $role->description);
	}
	
	/** @test */
	public function it_will_return_the_description_for_the_fallback_locale_if_no_description_was_provided_for_the_current_locale()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'description' => [
							'en' => 'Role A description - en',
							'es' => 'Role A description - es',
						],
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		app()->setLocale('fr');
		
		$this->assertEquals('en', config('app.fallback_locale'));
		$this->assertEquals('Role A description - en', $role->description);
	}
	
	/** @test */
	public function it_will_return_null_if_no_description_was_provided_for_the_locale_or_the_fallback_locale()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'description' => [
							'de' => 'Role A description - en',
							'es' => 'Role A description - es',
						],
					],
				],
			],
		]);
		
		$role = new Role('roleB');
		app()->setLocale('fr');
		
		$this->assertEquals('en', config('app.fallback_locale'));
		$this->assertNull($role->description);
	}
	
	/** @test */
	public function it_will_return_null_if_no_description_was_provided()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
					],
				],
			],
		]);
		
		$role = new Role('roleA');
		
		$this->assertNull($role->description);
	}
	
	/** @test */
	public function it_will_return_null_for_a_null_role()
	{
		$role = new Role(null);
		
		$this->assertNull($role->label);
		$this->assertNull($role->description);
	}
}