<?php

declare(strict_types=1);

/**
 * Update Convictions Penalties Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateConvictionsPenaltiesStatus;

/**
 * Update Convictions Penalties Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateConvictionsPenaltiesStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = UpdateConvictionsPenaltiesStatus::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
