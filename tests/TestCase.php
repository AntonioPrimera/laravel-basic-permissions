<?php

namespace AntonioPrimera\ConfigPermissions\Tests;

use AntonioPrimera\ConfigPermissions\ConfigPermissionsServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class TestCase extends \Orchestra\Testbench\TestCase
{
	use RefreshDatabase;

	/**
	 * Override this and provide a list of:
	 * [ 'path/to/migration/file1.php.stub' => 'MigrationClass1', ... ]
	 *
	 * @var array
	 */
	protected $migrate = [
		__DIR__ . '/TestContext/migrations/create_users_table.php.stub' => 'CreateUsersTable',
		__DIR__ . '/../database/migrations/add_role_to_users_table.php.stub' => 'AddRoleToUsersTable',
	];
	
	protected function getEnvironmentSetUp($app)
	{
		if ($this->migrate)
			$this->runPackageMigrations();
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function runPackageMigrations()
	{
		//this will reset the database
		Artisan::call('migrate:fresh');
		
		//import all migration files
		foreach ($this->migrate as $migrationFile => $migrationClass) {
			include_once $migrationFile;
			(new $migrationClass)->up();
		}
	}
}