<?php

namespace AntonioPrimera\BasicPermissions\Tests\TestContext\Traits;

trait TestContexts
{
	protected function setupContextComplexRoleSet()
	{
		config([
			'permissions' => [
				'roles' => [
					'roleA' => [
						'permissions' => [
							'A1' => [
								'c', 'r', 'u', 'd'
							],
							'A2' => [
								'c' => [
									's', 'm'
								],
								'r' => [
									's', 'm',
								],
								'u' => [
									's'
								],
								'd' => [
									'x'
								],
							],
							'A3' => '*'
						],
						
						'roles' => [
							'roleB',
							'roleC',
							'roleD',
							'roleE',
						],
					],
					
					'roleB' => [
						'permissions' => [
							'action-1',
							'action-2',
						],
						
						'roles' => [
							'roleF',
							'roleG',
							'roleE',
						],
					],
					
					'roleC' => [
						'permissions' => [
							'action-1',
						],
					],
					
					'roleD' => [
						'permissions' => [],
						
						'roles' => [
							'roleH', 'roleI', 'roleJ',
						],
					],
					
					'roleE' => [
						'roles' => [
							'roleY', 'roleK',
						],
					],
					
					'roleF' => [],
					'roleG' => [],
					'roleH' => [],
					'roleI' => [],
					
					'roleJ' => [
						'roles' => [
							'roleL',
						],
					],
					
					'roleK' => [
						'roles' => [
							'roleB', 'roleX',
						]
					],
					
					'roleL' => [
						'permissions' => [
							'L1' => [
								'c', 'r', 'u', 'd'
							],
							'L2' => [
								'c' => [
									's', 'm'
								],
								'r' => [
									's', 'm',
								],
								'u' => [
									's'
								],
								'd' => [
									'x'
								],
							],
							'L3' => '*',
							
							'L4-c',
							'L4-r',
						],
						
						'roles' => [
							'roleM', 'roleK', 'roleD',
						],
					],
					
					'roleM' => [],
					'roleX' => [],
					'roleY' => [],
				]
			]
		]);
	}
}