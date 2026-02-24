<?php

declare(strict_types=1);

/**
 * CreateGoodsVehicleList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\DiscPrinting;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\CreateGoodsVehicleList as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * CreateGoodsVehicleList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateGoodsVehicleListTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(['licences' => [1, 2], 'user' => 1])
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['licences' => [1, 2], 'user' => 1], $result);
    }
}
