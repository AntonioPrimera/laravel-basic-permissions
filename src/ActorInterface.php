<?php
namespace AntonioPrimera\BasicPermissions;

interface ActorInterface
{
	
	/**
	 * Get all roles belonging to the actor
	 */
	public function getRole() : Role;
	
	/**
	 * Set the role on the model and save the model
	 */
	public function setRole(string $role): static;
	
	/**
	 * Check if the actor has a given permission
	 */
	public function hasPermission(string $permission) : bool;
	
	/**
	 * Check if the actor has all permissions in a given list
	 */
	public function hasAllPermissions(iterable $permissions) : bool;
	
	/**
	 * Check if the actor has at least one of the given permissions
	 */
	public function hasAnyPermission(iterable $permissions) : bool;
	
}