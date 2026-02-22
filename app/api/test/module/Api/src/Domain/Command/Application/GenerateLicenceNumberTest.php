<?php

declare(strict_types=1);

/**
 * Generate Licence Number Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber;

/**
 * Generate Licence Number Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenerateLicenceNumberTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = GenerateLicenceNumber::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
