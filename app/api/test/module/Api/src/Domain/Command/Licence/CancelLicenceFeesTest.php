<?php

declare(strict_types=1);

/**
 * Cancel Licence Fees Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\CancelLicenceFees;

/**
 * Cancel Licence Fees Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CancelLicenceFeesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CancelLicenceFees::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
