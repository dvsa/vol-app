<?php

declare(strict_types=1);

/**
 * Message Consumer Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue;

use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\MessageConsumerInterface;
use Dvsa\Olcs\Cli\Service\Queue\MessageConsumerManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

class MessageConsumerManagerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp(): void
    {
        $sm = m::mock(ServiceManager::class);

        $sm->shouldReceive('setService')
            ->andReturnUsing(
                function ($alias, $service) use ($sm) {
                    $sm->shouldReceive('get')->with($alias)->andReturn($service);
                    $sm->shouldReceive('has')->with($alias)->andReturn(true);
                    return $sm;
                }
            );

        $this->sm = $sm;

        $this->sut = new MessageConsumerManager($sm);
    }

    public function testValidate(): void
    {
        $plugin = m::mock(MessageConsumerInterface::class);

        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }
}
