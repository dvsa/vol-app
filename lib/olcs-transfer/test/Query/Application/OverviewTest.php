<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\Application\Overview;

/**
 * Application test
 */
final class OverviewTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Overview::create(['id' => 111, 'foo' => 'bar', 'validateAppCompletion' => true]);

        $this->assertEquals(111, $command->getId());
        $this->assertTrue($command->getValidateAppCompletion());
    }
}
