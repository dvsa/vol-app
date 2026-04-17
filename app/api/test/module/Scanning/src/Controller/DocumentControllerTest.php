<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Scanning\Controller;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Document controller test
 */
class DocumentControllerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $response;
    /**
     * @var \Dvsa\Olcs\Scanning\Controller\DocumentController|m\Mock
     */
    protected $sut;

    /**
     * @var m\Mock
     */
    private $mockCommandHandlerManager;

    /**
     * @var \Laminas\Http\PhpEnvironment\Request|m\Mock
     */
    private $request;

    protected function setUp(): void
    {
        $this->request  = m::mock(\Laminas\Http\Request::class)->makePartial();
        $this->response = m::mock(\Laminas\Http\Response::class)->makePartial();

        $this->sut = m::mock(\Dvsa\Olcs\Scanning\Controller\DocumentController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);

        $this->mockCommandHandlerManager = m::mock(CommandHandlerManager::class);
        $this->sut->setCommandHandlerManager($this->mockCommandHandlerManager);
    }

    public function testMissingDescription(): void
    {
        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);
        $this->assertSame(400, $jsonModel->getVariable('status'));
        $this->assertSame('POST "description" is not a valid number', $jsonModel->getVariable('title'));
    }

    public function testInvalidDescription(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('X');
        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);
        $this->assertSame(400, $jsonModel->getVariable('status'));
        $this->assertSame('POST "description" is not a valid number', $jsonModel->getVariable('title'));
    }

    public function testMissingImage(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(400, $jsonModel->getVariable('status'));
        $this->assertSame('POST "image" is missing', $jsonModel->getVariable('title'));
    }

    public function testInvalidImage(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn('FOO');
        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(400, $jsonModel->getVariable('status'));
        $this->assertSame('File was not found', $jsonModel->getVariable('title'));
    }

    public function testInvalidImageValidator(): void
    {
        // No need to test all validation outcomes as were not testing the validator itself
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn(
            ['name' => 'foo', 'tmp_name' => 'bar', 'error' => 6]
        );
        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(400, $jsonModel->getVariable('status'));
        $this->assertSame('Missing a temporary folder', $jsonModel->getVariable('title'));
    }

    public function testInvalidImageUnsupportedMediaType(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $imageUpload = ['name' => 'foo', 'tmp_name' => __FILE__];
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn($imageUpload);
        $this->sut->shouldReceive('validateRequest')->with(12, $imageUpload)->once();

        $e = new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(['SCAN_INVALID_MIME' => 'foo']);
        $this->mockCommandHandlerManager->shouldReceive('handleCommand')->once()->andThrow($e);

        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(415, $jsonModel->getVariable('status'));
        $this->assertSame('Unsupported Media Type', $jsonModel->getVariable('title'));
    }

    public function testInvalidImageScanDocumentNotFound(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $imageUpload = ['name' => 'foo', 'tmp_name' => __FILE__];
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn($imageUpload);
        $this->sut->shouldReceive('validateRequest')->with(12, $imageUpload)->once();

        $e = new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(['SCAN_NOT_FOUND' => 'foo']);
        $this->mockCommandHandlerManager->shouldReceive('handleCommand')->once()->andThrow($e);

        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(400, $jsonModel->getVariable('status'));
        $this->assertSame('Cannot find scan record', $jsonModel->getVariable('title'));
    }

    public function testInvalidImageDomainError(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $imageUpload = ['name' => 'foo', 'tmp_name' => __FILE__];
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn($imageUpload);
        $this->sut->shouldReceive('validateRequest')->with(12, $imageUpload)->once();
        $this->sut->shouldReceive('logError')->with('Error processing scan document', ['FOO' => 'foo'])->once();

        $e = new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(['FOO' => 'foo']);
        $this->mockCommandHandlerManager->shouldReceive('handleCommand')->once()->andThrow($e);

        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(500, $jsonModel->getVariable('status'));
        $this->assertSame('Internal Server Error', $jsonModel->getVariable('title'));
    }

    public function testInvalidImageOtherError(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $imageUpload = ['name' => 'foo', 'tmp_name' => __FILE__];
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn($imageUpload);
        $this->sut->shouldReceive('validateRequest')->with(12, $imageUpload)->once();
        $this->sut->shouldReceive('logError')->with('Error processing scan document', ['message' => 'FOO'])->once();

        $e = new \Exception('FOO');
        $this->mockCommandHandlerManager->shouldReceive('handleCommand')->once()->andThrow($e);

        /** @var \Laminas\View\Model\JsonModel $jsonModel */
        $jsonModel = $this->sut->create([]);

        $this->assertSame(500, $jsonModel->getVariable('status'));
        $this->assertSame('Internal Server Error', $jsonModel->getVariable('title'));
    }

    public function testSuccess(): void
    {
        $this->request->shouldReceive('getPost')->with('description')->once()->andReturn('12');
        $imageUpload = ['name' => 'foo', 'tmp_name' => __DIR__  . DIRECTORY_SEPARATOR . 'test.file'];
        $this->request->shouldReceive('getFiles->get')->with('image')->once()->andReturn($imageUpload);
        $this->sut->shouldReceive('validateRequest')->with(12, $imageUpload)->once();
        $this->sut->shouldNotReceive('logError');

        $this->mockCommandHandlerManager->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) {
                /** @var \Dvsa\Olcs\Transfer\Command\Scan\CreateDocument $dto */
                $this->assertSame(12, $dto->getScanId());
                $this->assertSame('VEVTVDE=', $dto->getContent());
                $this->assertSame('foo', $dto->getFilename());
            }
        );

        /** @var \Laminas\Http\PhpEnvironment\Response $response*/
        $response = $this->sut->create([]);
        $this->assertSame(204, $response->getStatusCode());
    }
}
