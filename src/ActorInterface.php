<?php

namespace AntonioPrimera\ConfigPermissions;

interface ActorInterface
{
	
	/**
	 * Get all roles belonging to the actor
	 *
	 * @return Role
	 */
	public function getRole() : Role;
	
	/**
	 * Check if the actor has a given permission
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasPermission(string $permission) : bool;
	
	/**
	 * Check if the actor has all permissions in a given list
	 *
	 * @param iterable $permissions
	 *
	 * @return bool
	 */
	public function hasAllPermissions(iterable $permissions) : bool;
	
	/**
	 * Check if the actor has at least one of the given permissions
	 *
	 * @param iterable $permissions
	 *
	 * @return bool
	 */
	public function hasAnyPermission(iterable $permissions) : bool;
	
}