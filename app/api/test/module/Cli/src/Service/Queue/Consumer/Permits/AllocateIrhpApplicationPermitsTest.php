<?php

declare(strict_types=1);

/**
 * Allocate IRHP Application Permits Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\AllocateIrhpApplicationPermits as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Allocate IRHP Application Permits Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AllocateIrhpApplicationPermitsTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setEntityId(763);

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['id' => 763], $result);
    }
}
