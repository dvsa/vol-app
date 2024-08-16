<?php

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

        // Mock the logger
        $this->logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($this->logWriter);

        Logger::setLogger($logger);
    }

    protected function shouldErrorWith($code)
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

    public function testCreate()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->create([]);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testDelete()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->delete(3);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testDeleteList()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->deleteList();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testGet()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->get(2);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testGetList()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->getList();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testHead()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->head(1);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testOptions()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->options();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testPatch()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->patch(1, []);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testReplaceList()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->replaceList([]);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testPatchList()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->patchList([]);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testUpdate()
    {
        $this->shouldErrorWith(405);

        $response = $this->sut->update(3, []);

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Method Not Allowed', $response->getVariable('title'));
    }

    public function testNotFoundAction()
    {
        $this->shouldErrorWith(404);

        $response = $this->sut->notFoundAction();

        $this->assertInstanceOf(\Laminas\View\Model\JsonModel::class, $response);

        $this->assertEquals('Page Not Found', $response->getVariable('title'));
    }
}
