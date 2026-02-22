<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitWindow\Close;

/**
 * Close IRHP Permit Window Test
 */
class CloseTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = Close::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
