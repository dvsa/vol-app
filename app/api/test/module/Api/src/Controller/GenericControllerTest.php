<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Controller;

use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Controller\GenericController;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Mvc\Controller\Plugin\Response;
use Dvsa\Olcs\Transfer\Command\Application\UpdateTypeOfLicence;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Mockery as m;
use Olcs\Logging\Log\Logger;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\View\Model\JsonModel;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Controller\GenericController::class)]
class GenericControllerTest extends TestCase
{
    protected $commandHandlerManager;
    protected $queryHandlerManager;

    public function setUp(): void
    {
        $this->commandHandlerManager = m::mock(CommandHandlerManager::class);
        $this->queryHandlerManager = m::mock(QueryHandlerManager::class);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    public function testGet(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $data = ['foo' => 'var'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('singleResult')->with($data)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')->with($application)->andReturn($data);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetNotFound(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notFound')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow(new Exception\NotFoundException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetNotReady(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notReady')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow(new Exception\NotReadyException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetRestResponseError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $ex = new Exception\RestResponseException('blargle', HttpResponse::STATUS_CODE_500);

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, ['blargle'])->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetForbiddenError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $ex = new Exception\ForbiddenException('blargle');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_403, ['blargle'])->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->get(25);

        $this->assertSame($viewModel, $response);
    }

    public function testGetList(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $data = ['foo' => 'var'];
        $extra = ['bar' => 'cake'];
        $count = 54;
        $countUnfiltered = 60;

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('multipleResults')->with($count, $data, $countUnfiltered, $extra)
            ->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andReturn(
                [
                    'result' => $data,
                    'count' => $count,
                    'count-unfiltered' => $countUnfiltered,
                    'bar' => 'cake'
                ]
            );

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->getList();

        $this->assertSame($viewModel, $response);
    }

    public function testGetListStream(): void
    {
        $dto = new Application();

        $mockStream = m::mock(\Laminas\Http\Response\Stream::class);

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('streamResult')->with($mockStream)->andReturn('EXPECT');

        $mockParams = m::mock(Params::class)
            ->shouldReceive('__invoke')->with('dto')->andReturn($dto)
            ->getMock();

        $this->queryHandlerManager->shouldReceive('handleQuery')->with($dto)->andReturn($mockStream)
            ->getMock();

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        //  call & check
        $actual = $this->setupSut($mockSl)->getList();

        static::assertSame('EXPECT', $actual);
    }

    public function testGetListSingle(): void
    {
        $singleData = ['id' => 100];

        $dto = new Application();

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class);

        $mockParams = m::mock(Params::class)
            ->shouldReceive('__invoke')->with('dto')->andReturn($dto)
            ->getMock();

        $this->queryHandlerManager->shouldReceive('handleQuery')->with($dto)->andReturn($mockResult)
            ->getMock();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('singleResult')->with($mockResult)->andReturn($singleData);

        $mockSl = m::mock(PluginManager::class)
            ->shouldReceive('get')->with('params', null)->andReturn($mockParams)
            ->shouldReceive('get')->with('QueryHandlerManager')->andReturn($this->queryHandlerManager)
            ->shouldReceive('get')->with('response', null)->andReturn($mockResponse)
            ->shouldReceive('setController')
            ->getMock();

        //  call & check
        $actual = $this->setupSut($mockSl)->getList();

        static::assertSame($singleData, $actual);
    }

    public function testGetListNotFound(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notFound')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow(new Exception\NotFoundException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->getList();

        $this->assertSame($viewModel, $response);
    }

    public function testGetListClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->getList();

        $this->assertSame($viewModel, $response);
    }

    public function testGetListServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->getList();

        $this->assertSame($viewModel, $response);
    }

    public function testGetListNotReadyError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $ex = new Exception\NotReadyException('blargle');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notReady')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->getList();

        $this->assertSame($viewModel, $response);
    }

    public function testGetListForbiddenExceptionError(): void
    {
        $viewModel = new JsonModel();
        $application = new Application();
        $ex = new Exception\ForbiddenException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_403, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->queryHandlerManager->shouldReceive('handleQuery')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->getList();

        $this->assertSame($viewModel, $response);
    }

    public function testUpdate(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $result = new Result();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('successfulUpdate')->with($result)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andReturn($result);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateNotFound(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notFound')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\NotFoundException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateConflict(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(
            409,
            [
                'VER_CONF' => 'The resource you are editing is out of date'
            ]
        )->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\VersionConflictException('foo'));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateOptimisticLock(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(
            409,
            ['foo']
        )->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new OptimisticLockException('foo', m::mock()));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateRestResponseError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new Exception\RestResponseException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testUpdateForbiddenError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new Exception\ForbiddenException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_403, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->update(25, []);

        $this->assertSame($viewModel, $response);
    }

    public function testReplaceList(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $result = new Result();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('successfulUpdate')->with($result)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andReturn($result);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->replaceList(25);

        $this->assertSame($viewModel, $response);
    }

    public function testReplaceListNotFound(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notFound')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\NotFoundException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->replaceList(25);

        $this->assertSame($viewModel, $response);
    }

    public function testReplaceListClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->replaceList(25);

        $this->assertSame($viewModel, $response);
    }

    public function testReplaceListConflict(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(
            409,
            [
                'VER_CONF' => 'The resource you are editing is out of date'
            ]
        )->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\VersionConflictException('foo'));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->replaceList(25);

        $this->assertSame($viewModel, $response);
    }

    public function testReplaceListServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->replaceList(25);

        $this->assertSame($viewModel, $response);
    }

    public function testCreate(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $result = new Result();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('successfulCreate')->with($result)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andReturn($result);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->create([]);

        $this->assertSame($viewModel, $response);
    }

    public function testCreateClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->create([]);

        $this->assertSame($viewModel, $response);
    }

    public function testCreateServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->create([]);

        $this->assertSame($viewModel, $response);
    }

    public function testCreateRestResponseError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new Exception\RestResponseException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->create([]);

        $this->assertSame($viewModel, $response);
    }

    public function testCreateForbiddenError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new Exception\ForbiddenException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_403, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->create([]);

        $this->assertSame($viewModel, $response);
    }

    public function testDelete(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $result = new Result();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('successfulUpdate')->with($result)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andReturn($result);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->delete(25);

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteNotFound(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notFound')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\NotFoundException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->delete(25);

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->delete(25);

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->delete(25);

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteForbiddenError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new Exception\ForbiddenException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_403, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->delete(25);

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteList(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $result = new Result();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('successfulUpdate')->with($result)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andReturn($result);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->deleteList();

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteListNotFound(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('notFound')->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\NotFoundException());

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->deleteList();

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteListClientError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $errors = ['foo' => 'is not bar'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_400, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow(new Exception\ValidationException($errors));

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->deleteList();

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteListServerError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new \Exception('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_500, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->deleteList();

        $this->assertSame($viewModel, $response);
    }

    public function testDeleteListForbiddenError(): void
    {
        $viewModel = new JsonModel();
        $application = new UpdateTypeOfLicence();
        $ex = new Exception\ForbiddenException('blargle');
        $errors = ['blargle'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('error')->with(HttpResponse::STATUS_CODE_403, $errors)->andReturn($viewModel);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($application);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($application)
            ->andThrow($ex);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $response = $sut->deleteList();

        $this->assertSame($viewModel, $response);
    }

    /**
     * @param $mockSl
     * @return GenericController
     */
    protected function setupSut(mixed $mockSl): mixed
    {
        $sut = new GenericController($this->queryHandlerManager, $this->commandHandlerManager);
        $sut->setPluginManager($mockSl);
        return $sut;
    }

    /**
     * @param $mockResponse
     * @param $mockParams
     * @param $mockQueryHandler
     * @return m\MockInterface
     */
    protected function getMockSl(
        mixed $mockResponse,
        mixed $mockParams
    ): m\MockInterface {
        $mockSl = m::mock(PluginManager::class);
        $mockSl->shouldReceive('get')->with('response', null)->andReturn($mockResponse);
        $mockSl->shouldReceive('get')->with('params', null)->andReturn($mockParams);
        $mockSl->shouldReceive('setController');
        return $mockSl;
    }
}
