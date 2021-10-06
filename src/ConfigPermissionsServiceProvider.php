<?php

namespace AntonioPrimera\ConfigPermissions;

use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

class ConfigPermissionsServiceProvider extends PackageServiceProvider
{
	
	public function configurePackage(Package $package): void
	{
		$package
			->name('antonioprimera/config-permissions')
			->hasMigration('add_role_to_users_table');
		
			//->hasConfigFile()
			//->hasViews()
			//->hasViewComponent('spatie', Alert::class)
			//->hasViewComposer('*', MyViewComposer::class)
			//->sharesDataWithAllViews('downloads', 3)
			//->hasTranslations()
			//->hasAssets()
			//->hasRoute('web')
			//->hasMigration('create_package_tables')
			//->hasCommand(YourCoolPackageCommand::class);
	}
}