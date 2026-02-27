<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context;

use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;
use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Dvsa\Olcs\Api\Service\Publication\Context\AddressFormatterInitializer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class AddressFormatterInitializerTest extends MockeryTestCase
{
    private AddressFormatterInitializer $sut;

    public function setUp(): void
    {
        $this->sut = new AddressFormatterInitializer();
    }

    public function testInvokeWhenInstanceAddressFormatterAware(): void
    {
        $formatAddress = m::mock(FormatAddress::class);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with('AddressFormatter')
            ->andReturn($formatAddress);

        $instance = m::mock(AddressFormatterAwareInterface::class);
        $instance->shouldReceive('setAddressFormatter')
            ->with($formatAddress)
            ->once();

        $this->assertSame(
            $instance,
            ($this->sut)($container, $instance)
        );
    }

    public function testInvokeWhenInstanceNotAddressFormatterAware(): void
    {
        $container = m::mock(ContainerInterface::class);

        $instance = m::mock(stdClass::class);
        $instance->shouldReceive('setAddressFormatter')
            ->never();

        $this->assertSame(
            $instance,
            ($this->sut)($container, $instance)
        );
    }

    public function testInitializeWhenInstanceAddressFormatterAware(): void
    {
        $formatAddress = m::mock(FormatAddress::class);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with('AddressFormatter')
            ->andReturn($formatAddress);

        $instance = m::mock(AddressFormatterAwareInterface::class);
        $instance->shouldReceive('setAddressFormatter')
            ->with($formatAddress)
            ->once();

        $this->assertSame(
            $instance,
            $this->sut->__invoke($container, $instance)
        );
    }

    public function testInitializeWhenInstanceNotAddressFormatterAware(): void
    {
        $container = m::mock(ContainerInterface::class);

        $instance = m::mock(stdClass::class);
        $instance->shouldReceive('setAddressFormatter')
            ->never();

        $this->assertSame(
            $instance,
            $this->sut->__invoke($container, $instance)
        );
    }
}
