<?php

namespace AntonioPrimera\BasicPermissions;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class RoleCast implements CastsAttributes
{
	
	public function get($model, string $key, $value, array $attributes)
	{
		return new Role($value);
	}
	
	public function set($model, string $key, $value, array $attributes)
	{
		return $value instanceof Role ? $value->getName() : $value;
	}
}