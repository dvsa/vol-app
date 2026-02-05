<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Ebsr;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\ProcessPack as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * ProcessPack Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ProcessPackTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(json_encode(['foo' => 'bar']));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['foo' => 'bar'], $result);
    }
}
