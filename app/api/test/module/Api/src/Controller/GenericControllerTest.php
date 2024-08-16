<?php

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

/**
 * @covers \Dvsa\Olcs\Api\Controller\GenericController
 */
class GenericControllerTest extends TestCase
{
    protected $commandHandlerManager;
    protected $queryHandlerManager;

    public function setUp(): void
    {
        $this->commandHandlerManager = m::mock(CommandHandlerManager::class);
        $this->queryHandlerManager = m::mock(QueryHandlerManager::class);

        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);
    }

    public function testGet()
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

    public function testGetNotFound()
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

    public function testGetNotReady()
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

    public function testGetClientError()
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

    public function testGetServerError()
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

    public function testGetRestResponseError()
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

    public function testGetForbiddenError()
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

    public function testGetList()
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

    public function testGetListStream()
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

    public function testGetListSingle()
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

    public function testGetListNotFound()
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

    public function testGetListClientError()
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

    public function testGetListServerError()
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

    public function testGetListNotReadyError()
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

    public function testGetListForbiddenExceptionError()
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

    public function testUpdate()
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

    public function testUpdateNotFound()
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

    public function testUpdateClientError()
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

    public function testUpdateConflict()
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

    public function testUpdateOptimisticLock()
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

    public function testUpdateServerError()
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

    public function testUpdateRestResponseError()
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

    public function testUpdateForbiddenError()
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

    public function testReplaceList()
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

    public function testReplaceListNotFound()
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

    public function testReplaceListClientError()
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

    public function testReplaceListConflict()
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

    public function testReplaceListServerError()
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

    public function testCreate()
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

    public function testCreateClientError()
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

    public function testCreateServerError()
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

    public function testCreateRestResponseError()
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

    public function testCreateForbiddenError()
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

    public function testDelete()
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

    public function testDeleteNotFound()
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

    public function testDeleteClientError()
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

    public function testDeleteServerError()
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

    public function testDeleteForbiddenError()
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

    public function testDeleteList()
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

    public function testDeleteListNotFound()
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

    public function testDeleteListClientError()
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

    public function testDeleteListServerError()
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

    public function testDeleteListForbiddenError()
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
    protected function setupSut($mockSl)
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
        $mockResponse,
        $mockParams
    ) {
        $mockSl = m::mock(PluginManager::class);
        $mockSl->shouldReceive('get')->with('response', null)->andReturn($mockResponse);
        $mockSl->shouldReceive('get')->with('params', null)->andReturn($mockParams);
        $mockSl->shouldReceive('setController');
        return $mockSl;
    }
}
