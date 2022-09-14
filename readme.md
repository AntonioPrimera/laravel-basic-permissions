# Laravel Basic Permissions

## Overview

This package allows you to use simple roles with permissions in your application. Roles are configured in a
'permissions.php' config file and contain a list of roles, with their permissions (allowed actions) and
optionally a set of roles they inherit.

Here's a simple example of a `permissions` config file:

```php
return [
    'roles' => [
        'super-admin' 	=> [			//role name
            'permissions' => '*'        //permissions or wildcard
        ],
		
        'store-admin'	=> [			//role name
            'permissions' => [			//permission list (keyword: permissions)
                'store' => [			//scope
                    'manage'			//permission
                ],
                'items' => '*'
            ],
            
            'roles' => [				//inherited role list (keyword: roles / inherits)
                'customer',				//role
                'store-clerk',			//role
            ],
        ],
        
        'store-clerk' => [
            'permissions' => [
                'greet-customers',
                //... other permissions
            ],
        ],
        
        //other roles
    ],
];
```

You could add the 'store-admin' role to a user:

```php
$user->setRole('store-admin');
```

You can check different permissions on the user like so:

```php
$user->hasPermission('store:manage');           //true
$user->hasPermission('store:delete');           //false

$user->hasPermission('items.any-permission');   //true
$user->hasPermission('greet-customers');        //true (inherited from role: store-clerk)
```

## Installation

Install the package in your Laravel project via composer:

`composer require antonioprimera/laravel-basic-permissions`

The package exposes a migration, which adds a role column to the **users** table, so you should run the artisan migrate
command:

`php artisan migrate`

Add the `AntonioPrimera\BasicPermissions\ActorInterface` interface and the
`AntonioPrimera\BasicPermissions\ActorRolesAndPermissions` trait to your `User` model.

Next, you should add the custom attribute cast to your `User` model like so:

```php
protected $casts = [
    'role' => \AntonioPrimera\BasicPermissions\RoleCast::class,
    //...other attribute casts
];
```

This will cast the role attribute into a Role instance.


## Usage

The basic usage is demonstrated in the example in the **Overview** section above.

### Setting a role

Just add one of your configured roles, or the pre-configured `super-admin` role to a User model (or any other actor
implementing the ActorInterface from this package).

```php
$user->setRole('store-admin');
```

If you use the custom RoleCast class for the `role` attribute, you can set the role by assigning a Role instance to
the `role` attribute of the model:

```php
$user->role = new \AntonioPrimera\BasicPermissions\Role('store-admin');
$user->save();
```

### Querying a role permission

In your Laravel Policies, you can check whether the role assigned to an actor allows a specific action, meaning that
it has a specific permission (either directly, or inherited from another role):

```php
if ($user->hasPermission('store:manage'))
    //do something
```

### Querying several permissions at once

You can check whether an actor is allowed all permissions from a list of permissions:

```php
if ($user->hasAllPermissions(['store:manage', 'items:move', 'items:sell']))
    //do something only if the actor has all the above permissions
```

You can check whether an actor is allowed at least one permission from a list of permissions:

```php
if ($user->hasAnyPermission(['store:manage', 'items:move', 'items:sell']))
    //do something if the actor has at least one of the above permissions
```

## Configuration

You can configure your roles in your `permissions.php` config file, under the `roles` key, like the example from the
**Overview** section above.

Each role should have a list of permissions, maintained in its `permissions` or `actions` attribute and can optionally
have a `roles` or `inherits` attribute, specifying a list of inherited roles.

### Permissions

Permissions can be an array of simple strings (like `['greet-customer', ...]` in the example above), or nested
in arrays (like 'store:manage' in the example above). Nested permissions are addressed via their column (':')
separated path.

A star ('*') wildcard can be used for any permission level, which can be substituted with any permission (either direct
or nested). See the example from the **Overview** section above, where the 'store-admin' role has any permission
under the 'items' permission set. Also, you can see that the 'super-admin' role should be allowed any action, because
any permission query for this role will return true. *Note: the wildcard permission should be provided as a stand-alone
string, not inside an array*

Permission sets are like permission white-lists. A permission, which is not specifically added or inherited, will not
be allowed. For example, you can not deny a specific inherited permission for a role.

### Inherited roles

Once a role is defined, additionally to its own permissions, it can also inherit the permissions of other roles, which,
may also inherit other permissions themselves (any inheritance depth). Although, the application will not break if you
define roles with circular inheritance (e.g. role_1, inherits role_2, which inherits role_1), this might have
unexpected outcomes, so please try to avoid it.

You can use the `roles` array key to define the array of inherited roles. It's a matter of
preference, which key you use - their scope is identical.gacp

## Testing specific permissions

Because Testing is (or should be) an important part of the development process, this package has some built-in helpers,
to enable easy testing. Simply put, you can add and remove specific transient permissions to the actor instance. As
the label implies, these permissions are transient and will not be saved to the DB or to any config. They will be gone
the next time you fetch the actor from the DB (or create a new instance of this actor).

#### Assign a transient / temporary permission

In your tests, you might want to check that a specific action can be done only by actors with a specific permission.
To add a single permission to the actor, you can use the following syntax:

```php
$user->assignTemporaryPermission('some:permission:name');
```

All checks for this, permission will return true, until this user is destroyed and re-read from the DB. Please be aware
that the transient permissions are saved on the instance, so a `$user->refresh()` call will not reset these transient
permissions.

#### Remove a transient / temporary permission

You can remove a temporary (transient) permission from an actor, with the following method:

```php
$user->removeTemporaryPermission('some:permission:name');
```

#### Clear all transient / temporary permissions

You can remove all temporary (transient) permissions from an actor, with the following method:

```php
$user->clearTemporaryPermissions();
```
