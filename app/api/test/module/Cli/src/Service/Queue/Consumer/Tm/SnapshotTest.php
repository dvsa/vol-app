<?php

declare(strict_types=1);

/**
 * Snapshot Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Tm;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm\Snapshot as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Snapshot Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SnapshotTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setEntityId(111);
        $item->setCreatedBy($user);

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['id' => 111, 'user' => 1], $result);
    }
}
