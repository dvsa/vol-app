<?php

declare(strict_types=1);

/**
 * PrintDiscsList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\DiscPrinting;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\PrintDiscs as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * PrintDiscsList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintDiscsTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(['discs' => ['d1', 'd2'], 'type' => 'Goods', 'startNumber' => 7, 'user' => 1])
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['discs' => ['d1', 'd2'], 'type' => 'Goods', 'startNumber' => 7, 'user' => 1], $result);
    }
}
