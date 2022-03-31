<?php
namespace AntonioPrimera\BasicPermissions;

use Illuminate\Support\ServiceProvider;

class BasicPermissionsServiceProvider extends ServiceProvider
{
	
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/permissions.php', 'permissions');
	}
	
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		}
	}
}