<?php
namespace AntonioPrimera\BasicPermissions\Tests;

use AntonioPrimera\BasicPermissions\BasicPermissionsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class TestCase extends \Orchestra\Testbench\TestCase
{
	use RefreshDatabase;

	protected $migrate = [
		__DIR__ . '/TestContext/migrations/create_users_table.php.stub' => 'CreateUsersTable',
		//__DIR__ . '/../database/migrations/2022_03_30_000000_add_role_to_users_table.php' => 'AddRoleToUsersTable',
	];
	
	protected function getEnvironmentSetUp($app)
	{
		//this will reset the database
		Artisan::call('migrate:fresh');
		
		//import all migration files
		foreach ($this->migrate as $migrationFile => $migrationClass) {
			include_once $migrationFile;
			(new $migrationClass)->up();
		}
	}
	
	protected function getPackageProviders($app)
	{
		return [
			BasicPermissionsServiceProvider::class,
		];
	}
}