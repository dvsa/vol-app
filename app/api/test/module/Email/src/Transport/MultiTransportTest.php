<?php

namespace Dvsa\OlcsTest\Email\Transport;

use Dvsa\Olcs\Email\Transport\MultiTransport;
use Dvsa\Olcs\Email\Transport\MultiTransportOptions;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Mail\Message;
use Mockery as m;

/**
 * Class MultiTransportTest
 */
class MultiTransportTest extends MockeryTestCase
{
    public function testSetGet()
    {
        $mockMessage = m::mock(Message::class);

        $mockTransport1 = m::mock();
        $mockTransport1->shouldReceive('send')->with($mockMessage)->once();
        $mockTransport2 = m::mock();
        $mockTransport2->shouldReceive('send')->with($mockMessage)->once();

        $mockOptions = m::mock(MultiTransportOptions::class);
        $mockOptions->shouldReceive('getTransport')->with()->once()->andReturn([$mockTransport1, $mockTransport2]);

        $sut = new MultiTransport();
        $sut->setOptions($mockOptions);

        $sut->send($mockMessage);
    }
}
