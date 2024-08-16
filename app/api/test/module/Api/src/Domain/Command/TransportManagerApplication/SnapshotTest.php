<?php

/**
 * Snapshot
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication\Snapshot;

/**
 * Snapshot
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SnapshotTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = Snapshot::create(
            [
                'user' => 1,
            ]
        );

        $this->assertEquals(1, $command->getUser());
    }
}
