<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Controller;

use Dvsa\Olcs\Api\Controller\XmlController;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Http\Response as HttpResponse;
use Dvsa\Olcs\Api\Mvc\Controller\Plugin\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\PluginManager;

/**
 * Class XmlControllerTest
 */
class XmlControllerTest extends TestCase
{
    protected $commandHandlerManager;

    public function setUp(): void
    {
        $this->commandHandlerManager = m::mock(CommandHandlerManager::class);
    }

    /**
     * @param $mockSl
     * @return XmlController
     */
    protected function setupSut(mixed $mockSl): mixed
    {
        $sut = new XmlController($this->commandHandlerManager);
        $sut->setPluginManager($mockSl);
        return $sut;
    }

    /**
     * Tests create when a valid response is returned
     */
    public function testCreate(): void
    {
        $validResponse = new HttpResponse();
        $validResponse->setStatusCode(HttpResponse::STATUS_CODE_202);
        $command = m::mock(CommandInterface::class);
        $result = new Result();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('xmlAccepted')->andReturn($validResponse);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($command);

        $this->commandHandlerManager->shouldReceive('handleCommand')->with($command)->andReturn($result);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $controllerResponse = $sut->create([]);

        $this->assertSame($validResponse, $controllerResponse);
    }

    /**
     * Tests create when an exception is thrown
     */
    public function testCreateWithException(): void
    {
        $badRequestResponse = new HttpResponse();
        $badRequestResponse->setStatusCode(HttpResponse::STATUS_CODE_400);
        $command = m::mock(CommandInterface::class);

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('xmlBadRequest')->andReturn($badRequestResponse);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('__invoke')->with('dto')->andReturn($command);

        $this->commandHandlerManager->shouldReceive('handleCommand')
            ->with($command)
            ->andThrow(\Dvsa\Olcs\Api\Domain\Exception\Exception::class);

        $mockSl = $this->getMockSl($mockResponse, $mockParams);

        $sut = $this->setupSut($mockSl);

        $controllerResponse = $sut->create([]);

        $this->assertSame($badRequestResponse, $controllerResponse);
    }


    /**
     * @param $mockResponse
     * @param $mockParams
     * @param $mockCommandHandler
     * @return m\MockInterface
     */
    protected function getMockSl(mixed $mockResponse, mixed $mockParams): m\MockInterface
    {
        $mockSl = m::mock(PluginManager::class);
        $mockSl->shouldReceive('get')->with('response', null)->andReturn($mockResponse);
        $mockSl->shouldReceive('get')->with('params', null)->andReturn($mockParams);
        $mockSl->shouldReceive('setController');

        return $mockSl;
    }
}
