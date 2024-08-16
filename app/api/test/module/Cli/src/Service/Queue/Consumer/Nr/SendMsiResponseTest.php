<?php

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Nr;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Nr\SendMsiResponse as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * SendMsiResponse Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendMsiResponseTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData()
    {
        $item = new QueueEntity();
        $item->setOptions(json_encode(['foo' => 'bar']));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['foo' => 'bar'], $result);
    }
}
