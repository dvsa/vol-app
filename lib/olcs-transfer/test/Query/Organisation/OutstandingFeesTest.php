<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees;

/**
 * Outstanding Fees test
 */
final class OutstandingFeesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = OutstandingFees::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
    }
}
