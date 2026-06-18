<?php

namespace Dvsa\OlcsTest\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\Application\Overview;

/**
 * Application test
 */
class OverviewTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Overview::create(['id' => 111, 'foo' => 'bar', 'validateAppCompletion' => true]);

        $this->assertEquals(111, $command->getId());
        $this->assertTrue($command->getValidateAppCompletion());
    }
}
