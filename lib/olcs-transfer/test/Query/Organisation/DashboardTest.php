<?php

namespace Dvsa\OlcsTest\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard;

/**
 * Dashboard test
 */
class DashboardTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Dashboard::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
