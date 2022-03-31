<?php
namespace AntonioPrimera\BasicPermissions\Tests\TestContext\Models;

use AntonioPrimera\BasicPermissions\ActorInterface;
use AntonioPrimera\BasicPermissions\ActorRolesAndPermissions;
use Illuminate\Database\Eloquent\Model;

class TestUser extends Model implements ActorInterface
{
	use ActorRolesAndPermissions;
	
	protected $table = 'users';
	protected $guarded = [];
	public $timestamps = false;
	
}