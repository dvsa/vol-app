<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\QueryHandler\QueryHandlerInterface;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\HandlerInterface;
use Dvsa\Olcs\Api\Domain\ValidationHandlerManager;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

class QueryHandlerManagerTest extends MockeryTestCase
{
    /**
     * @var QueryHandlerManager
     */
    private $sut;

    private $vhm;

    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->vhm = m::mock(ValidationHandlerManager::class)->makePartial();

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('ValidationHandlerManager')->andReturn($this->vhm);

        $this->sut = new QueryHandlerManager($container, []);
    }

    public function testHandleQuery(): void
    {
        $query = m::mock(QueryInterface::class)->makePartial();
        $query->shouldReceive('getArrayCopy')->once()->andReturn(['foo' => 'bar']);

        $mockService = m::mock(QueryHandlerInterface::class);
        $mockService->shouldReceive('checkEnabled')->once()->andReturn(true);
        $mockService->shouldReceive('handleQuery')->with($query)->andReturn(['response']);

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($query)->andReturn(true);
        $this->vhm->setService($mockService::class, $mockValidator);

        $this->sut->setService($query::class, $mockService);

        $this->assertEquals(['response'], $this->sut->handleQuery($query, true));
    }

    public function testHandleQueryReturningEntity(): void
    {
        $query = m::mock(QueryInterface::class)->makePartial();
        $query->shouldReceive('getArrayCopy')->once()->andReturn(['foo' => 'bar']);

        $response = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $response->shouldReceive('serialize')->with()->once()->andReturn(['response']);

        $mockService = m::mock(QueryHandlerInterface::class);
        $mockService->shouldReceive('checkEnabled')->once()->andReturn(true);
        $mockService->shouldReceive('handleQuery')->with($query)->andReturn($response);

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($query)->andReturn(true);
        $this->vhm->setService($mockService::class, $mockValidator);

        $this->sut->setService($query::class, $mockService);

        $this->assertEquals($response, $this->sut->handleQuery($query, true));
    }

    public function testHandleQueryFailingValidator(): void
    {
        $this->expectException(ForbiddenException::class);

        $query = m::mock(QueryInterface::class)->makePartial();
        $query->shouldReceive('getArrayCopy')->twice()->andReturn(['foo' => 'bar']);

        $mockService = m::mock(QueryHandlerInterface::class);
        $mockService->shouldReceive('checkEnabled')->once()->andReturn(true);
        $mockService->shouldReceive('handleQuery')->never();

        $mockValidator = m::mock(HandlerInterface::class);
        $mockValidator->shouldReceive('isValid')->with($query)->andReturn(false);
        $this->vhm->setService($mockService::class, $mockValidator);

        $this->sut->setService($query::class, $mockService);

        $this->sut->handleQuery($query, true);
    }

    public function testHandleQueryInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $query = m::mock(QueryInterface::class)->makePartial();

        $mockService = m::mock();
        $mockService->shouldReceive('handleQuery')->with($query)->andReturn(['response']);

        $this->sut->setService($query::class, $mockService);

        $this->sut->handleQuery($query);
    }

    public function testValidate(): void
    {
        $plugin = m::mock(QueryHandlerInterface::class);
        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);
        $this->sut->validate(null);
    }
}
