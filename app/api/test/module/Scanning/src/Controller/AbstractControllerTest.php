<?php

declare(strict_types=1);

/**
 * Abstract controller test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Scanning\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

/**
 * Abstract controller test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $response;

    /**
     * @var \Laminas\Log\Writer\Mock
     */
    protected $logWriter;

    protected function setUp(): void
    {
        $this->response = m::mock(\Laminas\Http\Response::class)->makePartial();

        $this->sut = m::mock(\Dvsa\Olcs\Scanning\Controller\AbstractController::class)->makePartial();
        $this->sut->shouldReceive('getResponse')
            ->andReturn($this->response);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    protected function shouldErrorWith(mixed $code): void
    {
        $this->response->shouldReceive('setStatusCode')
            ->with($code)
            ->andReturnSelf()
            ->shouldReceive('getHeaders')
            ->andReturn(
                m::mock()
                ->shouldReceive('addHeaderLine')
                ->with('Content-Type', 'application/problem+json')
                ->getMock()
            );
    }

    public function testCreate(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->create([]);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testDelete(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->delete(3);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testDeleteList(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->deleteList();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testGet(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->get(2);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testGetList(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->getList();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testHead(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->head(1);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testOptions(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->options();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testPatch(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->patch(1, []);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testReplaceList(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->replaceList([]);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testPatchList(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->patchList([]);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testUpdate(): void
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->update(3, []);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testNotFoundAction(): void
    {
        $this->shouldErrorWith(404);

        $response = $this->sut->notFoundAction();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Page Not Found', $response->getVariable('title'));
    }
}
