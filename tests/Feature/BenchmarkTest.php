<?php
namespace AntonioPrimera\BasicPermissions\Tests\Feature;

use AntonioPrimera\BasicPermissions\Role;
use AntonioPrimera\BasicPermissions\Tests\TestCase;
use AntonioPrimera\BasicPermissions\Tests\TestContext\Traits\TestContexts;

class BenchmarkTest extends TestCase
{
	use TestContexts;
	
	/** @test */
	public function benchmark_permission_checks()
	{
		$this->setupContextComplexRoleSet();
		
		$allowedPermissions = [
			'A1:c', 'A1:r', 'A1:u', 'A1:d',
			'A2:c:s', 'A2:c:m',
			'A2:r:s', 'A2:r:m',
			'A2:u:s', 'A2:d:x',
			'A3:anything', 'A3:c:s',
			'action-1', 'action-2',
			'L1:c', 'L1:r',
			'L2:c:s', 'L2:d:x',
			'L3:anything', 'L3:c:s:x',
			'L4-c', 'L4-r',
		];
		
		$notAllowedPermissions = [
			'A1', 'A1:x', 'A1:c:s', 'A2:c:r', 'action-1:c', 'action-3',
			'L1:x', 'L2:c:x',' L4-x', 'L4-c:s', 'L4:c'
		];
		
		//$role = new Role('roleA');
		
		$runs = 500;
		$checks = ['successful' => 0, 'unsuccessful' => 0];
		
		$startTime = microtime(true);
		foreach (range(1, $runs) as $index) {
			$role = new Role('roleA');
			
			if ($role->hasPermission($allowedPermissions[random_int(0, count($allowedPermissions) - 1)]))
				$checks['successful']++;
			
			if (!$role->hasPermission($notAllowedPermissions[random_int(0, count($notAllowedPermissions) - 1)]))
				$checks['unsuccessful']++;
		}
		
		$totalChecks = $checks['successful'] + $checks['unsuccessful'];
		
		echo "\n=== Permission check benchmark =============================\n";
		echo now()->format('Y.m.d H:i:s') . " - total benchmark time for $totalChecks checks: "
			. (round((microtime(true) - $startTime) * 1000)) . " ms \n";
		echo "Executed " . $totalChecks . " checks: \n"
			. "    * " . $checks['successful'] . " allowed permissions \n"
			. "    * " . $checks['unsuccessful'] . " not allowed permissions \n";
		echo "------------------------------------------------------------\n";
		
		$this->expectNotToPerformAssertions();
	}
	
}

/*

=== Permission check benchmark =============================
2022.09.13 14:24:10 - total benchmark time for 1000 checks: 162 ms
Executed 1000 checks:
    * 500 allowed permissions
    * 500 not allowed permissions
------------------------------------------------------------

=== Permission check benchmark =============================
2022.09.13 18:31:11 - total benchmark time for 1000 checks: 119 ms
Executed 1000 checks:
    * 500 allowed permissions
    * 500 not allowed permissions
------------------------------------------------------------

=== Permission check benchmark =============================
2022.09.14 10:41:51 - total benchmark time for 1000 checks: 70 ms
Executed 1000 checks:
    * 500 allowed permissions
    * 500 not allowed permissions
------------------------------------------------------------

 */