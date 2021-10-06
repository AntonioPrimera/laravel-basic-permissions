<?php

namespace AntonioPrimera\ConfigPermissions\Tests\TestContext\Models;

use AntonioPrimera\ConfigPermissions\ActorInterface;
use AntonioPrimera\ConfigPermissions\ActorRolesAndPermissions;

class TestUser extends \Illuminate\Database\Eloquent\Model implements ActorInterface
{
	use ActorRolesAndPermissions;
	
	protected $table = 'users';
	protected $guarded = [];
	public $timestamps = false;
	
}