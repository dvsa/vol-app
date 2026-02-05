<?php

declare(strict_types=1);

/**
 * Update Business Type Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateBusinessTypeStatus;

/**
 * Update Business Type Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateBusinessTypeStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = UpdateBusinessTypeStatus::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
