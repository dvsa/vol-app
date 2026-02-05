<?php

declare(strict_types=1);

/**
 * Update Operating Centres Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateOperatingCentresStatus;

/**
 * Update Operating Centres Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateOperatingCentresStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = UpdateOperatingCentresStatus::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
