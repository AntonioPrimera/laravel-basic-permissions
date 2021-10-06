<?php

return [
	'roles' => [
		'super-admin' 	=> [			//role name
			'permissions' => '*'
		],
	],
	
	//--- Permission bag and Inherited role bag -----------------------------------------------------------------------
	
	//'store-admin'	=> [			//role name
	//	'permissions' => [			//permission list (keyword: permissions)
	//		'store' => [			//scope
	//			'manage'			//permission
	//		],
	//		'items' => '*'
	//	],
	//	'roles' => [				//inherited role list (keyword: roles / inherits)
	//		'customer',				//role
	//		'store-clerk',			//role
	//	],
	//],
];
