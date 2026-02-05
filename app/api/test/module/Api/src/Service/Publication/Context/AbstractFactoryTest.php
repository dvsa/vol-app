<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractFactory;
use Dvsa\OlcsTest\Api\Service\Publication\Context\Stub\AbstractContextStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Dvsa\Olcs\Api\Service\Publication\Context\AbstractFactory
 */
class AbstractFactoryTest extends MockeryTestCase
{
    /** @var ContainerInterface| m\MockInterface */
    private $mockSl;

    public function setUp(): void
    {
        $this->mockSl = m::mock(ContainerInterface::class);
    }

    public function testCanCreate(): void
    {
        $reqName = AbstractContextStub::class;

        static::assertTrue(
            (new AbstractFactory())->canCreate($this->mockSl, $reqName)
        );
    }

    public function testInvoke(): void
    {
        $this->mockSl
            ->shouldReceive('get')
            ->with('QueryHandlerManager')
            ->once()
            ->andReturn(
                m::mock(QueryHandlerManager::class)
            );

        $reqName = AbstractContextStub::class;

        static::assertInstanceOf(
            AbstractContextStub::class,
            (new AbstractFactory())($this->mockSl, $reqName)
        );
    }
}
