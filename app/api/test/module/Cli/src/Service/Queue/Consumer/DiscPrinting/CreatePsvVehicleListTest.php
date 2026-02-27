<?php

declare(strict_types=1);

/**
 * CreatePsvVehicleList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\DiscPrinting;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\CreatePsvVehicleList as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * CreatePsvVehicleList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreatePsvVehicleListTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(['bookmarks' => ['b1', 'b2'], 'queries' => ['q1', 'q2'], 'user' => 1])
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['bookmarks' => ['b1', 'b2'], 'queries' => ['q1', 'q2'], 'user' => 1], $result);
    }
}
