<?php

namespace OlcsTest\View\Helper\SessionTimeoutWarning;

use Interop\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning;
use Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarningFactory;
use Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarningFactoryConfigInputFilter;

class SessionTimeoutWarningFactoryTest extends MockeryTestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function testInvoke()
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Config')->andReturn([]);

        $mockInputFilter = m::mock(SessionTimeoutWarningFactoryConfigInputFilter::class)
            ->shouldIgnoreMissing();
        $mockInputFilter->shouldReceive('isValid')->andReturn(true);

        $mockInputFilter
            ->shouldReceive('getValue')
            ->with(SessionTimeoutWarningFactoryConfigInputFilter::CONFIG_ENABLED)
            ->andReturn(true);
        $mockInputFilter
            ->shouldReceive('getValue')
            ->with(SessionTimeoutWarningFactoryConfigInputFilter::CONFIG_SECONDS_BEFORE_EXPIRY_WARNING)
            ->andReturn(1);
        $mockInputFilter
            ->shouldReceive('getValue')
            ->with(SessionTimeoutWarningFactoryConfigInputFilter::CONFIG_TIMEOUT_REDIRECT_URL)
            ->andReturn("");

        $sut = new SessionTimeoutWarningFactory($mockInputFilter);
        $service = $sut->__invoke($container, SessionTimeoutWarning::class);

        $this->assertInstanceOf(SessionTimeoutWarning::class, $service);
    }

    /**
     * @test
     * @depends testInvoke
     */
    public function testInvokeWithInvalidConfigurationThrowsException()
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Config')->andReturn([]);

        $mockInputFilter = m::mock(SessionTimeoutWarningFactoryConfigInputFilter::class)->shouldIgnoreMissing();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unable to instantiate SessionTimeoutWarning due to invalid configuration:");

        $sut = new SessionTimeoutWarningFactory($mockInputFilter);
        $sut->__invoke($container, SessionTimeoutWarning::class);
    }
}
