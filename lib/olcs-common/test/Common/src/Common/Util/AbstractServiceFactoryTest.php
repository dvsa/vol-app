<?php

declare(strict_types=1);

namespace CommonTest\Util;

use Common\Util\AbstractServiceFactory;
use CommonTest\Common\Util\Stub\ServiceWithFactoryStub;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Util\AbstractServiceFactory::class)]
final class AbstractServiceFactoryTest extends MockeryTestCase
{
    /** @var  AbstractServiceFactory | m\MockInterface */
    protected $sut;

    /** @var  m\MockInterface | ContainerInterface */
    protected $mockSm;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = m::mock(AbstractServiceFactory::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockSm = m::mock(ContainerInterface::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanCreate')]
    public function testCanCreate($requestedName, $expect): void
    {
        $this->assertEquals($expect, $this->sut->canCreate($this->mockSm, $requestedName));
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return list{array{fqcn: 'Helper\Form', expect: true}, array{fqcn: 'Wrong\Wrong', expect: false}}
     */
    public static function dpTestCanCreate(): \Iterator
    {
        //  use real classes to test
        yield [
            //  @see \Common\Service\Helper\FormHelperService
            'requestedName' => 'Helper\Form',
            'expect' => true,
        ];
        yield [
            'requestedName' => 'Wrong\Wrong',
            'expect' => false,
        ];
    }

    public function testInvokeUsingFactory(): void
    {
        $className = 'unit_className';

        $this->sut->shouldReceive('getClassName')->with($className)->andReturn(ServiceWithFactoryStub::class);

        $actual = ($this->sut)($this->mockSm, $className);

        $this->assertInstanceOf(FactoryInterface::class, $actual);
        $this->assertInstanceOf(ServiceWithFactoryStub::class, $actual);
    }

    public function testInvoke(): void
    {
        $className = 'unit_className';

        $this->sut->shouldReceive('getClassName')->with($className)->andReturn('\stdClass');

        $actual = ($this->sut)($this->mockSm, $className);

        $this->assertInstanceOf('\stdClass', $actual);
    }
}
