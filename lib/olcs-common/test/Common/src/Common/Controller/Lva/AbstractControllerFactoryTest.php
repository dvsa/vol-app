<?php

namespace CommonTest\Controller\Lva;

use Common\Controller\Lva\AbstractControllerFactory;
use CommonTest\Common\Controller\Lva\Stubs\ControllerWithFactoryStub;
use Psr\Container\ContainerInterface;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Controller\Lva\AbstractControllerFactory
 */
class AbstractControllerFactoryTest extends MockeryTestCase
{
    /** @var  AbstractControllerFactory */
    protected $sut;

    /** @var  m\MockInterface | ControllerManager */
    protected $mockScm;

    /** @var  m\MockInterface | ContainerInterface */
    protected $mockSm;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new AbstractControllerFactory();
        $this->mockSm = m::mock(ContainerInterface::class);
    }

    /**
     * @group lva_abstract_factory
     */
    public function testCanCreate(): void
    {
        $requestedName = 'foo';

        $config = [
            'controllers' => [
                'lva_controllers' => [
                    'foo' => 'bar'
                ]
            ]
        ];

        $this->mockSm->shouldReceive('get')->with('Config')->andReturn($config);

        $this->assertTrue($this->sut->canCreate($this->mockSm, $requestedName));
    }

    /**
     * @group lva_abstract_factory
     */
    public function testCanCreateWithoutConfigMatch(): void
    {
        $requestedName = 'bar';

        $config = [
            'controllers' => [
                'lva_controllers' => [
                    'blap' => 'bar'
                ]
            ]
        ];

        $this->mockSm->shouldReceive('get')->with('Config')->andReturn($config);

        $this->assertFalse($this->sut->canCreate($this->mockSm, $requestedName));
    }

    /**
     * @group lva_abstract_factory
     */
    public function testInvoke(): void
    {
        $requestedName = 'foo';

        $config = [
            'controllers' => [
                'lva_controllers' => [
                    'foo' => '\stdClass'
                ]
            ]
        ];

        $this->mockSm->shouldReceive('get')->with('Config')->andReturn($config);

        $this->assertInstanceOf('\stdClass', ($this->sut)($this->mockSm, $requestedName));
    }

    public function testInvokeUsingFactory(): void
    {
        $requestedName = 'unit_reqName';

        $config = [
            'controllers' => [
                'lva_controllers' => [
                    $requestedName => ControllerWithFactoryStub::class,
                ],
            ],
        ];

        $this->mockSm->shouldReceive('get')->with('Config')->andReturn($config);

        $actual = ($this->sut)($this->mockSm, $requestedName);
        static::assertInstanceOf(FactoryInterface::class, $actual);
        static::assertInstanceOf(ControllerWithFactoryStub::class, $actual);
    }
}
