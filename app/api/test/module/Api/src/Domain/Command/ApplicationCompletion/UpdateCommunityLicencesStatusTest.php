<?php

/**
 * Update Community Licences Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateCommunityLicencesStatus;

/**
 * Update Community Licences Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateCommunityLicencesStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = UpdateCommunityLicencesStatus::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
