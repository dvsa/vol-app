<?php

declare(strict_types=1);

/**
 * Generate Batch Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;

/**
 * Generate Batch Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GenerateBatchTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = GenerateBatch::create(
            [
                'isBatchReprint' => true,
                'licence' => 1,
                'communityLicenceIds' => [10, 20],
                'identifier' => 2,
            ]
        );

        $this->assertEquals(true, $command->getIsBatchReprint());
        $this->assertEquals(1, $command->getLicence());
        $this->assertEquals([10, 20], $command->getCommunityLicenceIds());
        $this->assertEquals(2, $command->getIdentifier());
    }
}
