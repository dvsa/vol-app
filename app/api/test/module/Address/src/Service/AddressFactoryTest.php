<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Address\Service;

use Dvsa\Olcs\Address\Service\Address;
use Dvsa\Olcs\Address\Service\AddressFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AddressFactoryTest extends MockeryTestCase
{
    /**
     * @var AddressFactory
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new AddressFactory();
    }

    public function testInvokeWithoutConfig(): void
    {
        $this->expectException(\RuntimeException::class);

        $config = [];

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')
            ->with('config')
            ->andReturn($config);

        $this->sut->__invoke($sm, Address::class);
    }

    public function testInvoke(): void
    {
        $config = [
            'address' => [
                'client' => [
                    'baseuri' => 'foo'
                ]
            ]
        ];

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')
            ->with('config')
            ->andReturn($config);

        $address = $this->sut->__invoke($sm, Address::class);

        $this->assertInstanceOf(Address::class, $address);
    }
}
