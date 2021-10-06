<?php

namespace AntonioPrimera\ConfigPermissions\Tests;

use AntonioPrimera\ConfigPermissions\ConfigPermissionsServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class TestCase extends \Orchestra\Testbench\TestCase
{
	use RefreshDatabase;

	/**
	 * Override this and provide a
	 * list of migration files
	 *
	 * @var array | false
	 */
	protected $migrate = [
		__DIR__ . '/TestContext/migrations/create_users_table.php.stub',
		__DIR__ . '/../database/migrations/add_role_to_users_table.php.stub'
	];
	
	protected function setUp(): void
	{
		parent::setUp();
	}
	
	protected function getPackageProviders($app)
	{
		return [
			ConfigPermissionsServiceProvider::class,
		];
	}
	
	protected function getEnvironmentSetUp($app)
	{
		if ($this->migrate && is_array($this->migrate))
			$this->loadPackageMigrations($this->migrate);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	/**
	 * Load a list of migrations. When testing packages, no migrations are automatically loaded, so the list of
	 * migrations needs to be manually defined. Each migration needs to be added manually, in the correct
	 * order, because package migrations should not have timestamps as part of their names.
	 *
	 * @param array $migrations
	 * @param null  $migrationFolderPath
	 */
	protected function loadPackageMigrations(array $migrations, $migrationFolderPath = null)
	{
		//get the already declared classes, so we exclude them later
		$declaredClasses = get_declared_classes();
		
		//import all migration files
		foreach ($migrations as $migrationFile) {
			$fileName = $migrationFolderPath
				? Str::of($migrationFolderPath)->finish('/')->append($migrationFile)
				: $migrationFile;
			include_once $fileName;
		}
		
		//go through all newly declared classes (just imported) and if they are migrations, run them
		$newlyDeclaredClasses = array_diff(get_declared_classes(), $declaredClasses);
		
		foreach ($newlyDeclaredClasses as $newlyDeclaredClass) {
			if (is_subclass_of($newlyDeclaredClass, Migration::class))
				(new $newlyDeclaredClass)->up();
		}
	}
}