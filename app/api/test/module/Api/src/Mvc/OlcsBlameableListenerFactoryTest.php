<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Mvc;

use Dvsa\Olcs\Api\Mvc\OlcsBlameableListener;
use Dvsa\Olcs\Api\Mvc\OlcsBlameableListenerFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OlcsBlameableListenerFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $container = m::mock(ContainerInterface::class);
        $sut = new OlcsBlameableListenerFactory();
        $listener = $sut->__invoke($container, OlcsBlameableListener::class);

        $this->assertInstanceOf(OlcsBlameableListener::class, $listener);
    }
}
