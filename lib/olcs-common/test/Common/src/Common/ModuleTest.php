<?php

namespace CommonTest;

use Common\Module;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Mvc\Application;
use Laminas\Validator\Csrf;

/**
 * @covers \Common\Module
 */
class ModuleTest extends MockeryTestCase
{
    private static $cfg = [
        'csrf' => [
            'timeout' => 9999,
            'whitelist' => [
                'unit_whitelisted_path',
            ],
        ],
    ];

    /** @var Module */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockReq;

    /** @var  \Laminas\Mvc\MvcEvent | m\MockInterface */
    private $mockEvent;

    /** @var  ContainerInterface | m\MockInterface */
    private $mockSm;

    /** @var  m\MockInterface */
    private $mockApp;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Module();

        $this->mockReq = m::mock(\Laminas\Http\Request::class);

        $this->mockSm = m::mock(ContainerInterface::class);
        $this->mockSm->shouldReceive('get')->with('config')->andReturn(self::$cfg);

        $this->mockApp = m::mock(Application::class);
        $this->mockApp->shouldReceive('getServiceManager')->andReturn($this->mockSm);

        $this->mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $this->mockEvent
            ->shouldReceive('getRequest')->andReturn($this->mockReq);
    }

    public function testValidateCsrfTokenNotPost(): void
    {
        $this->mockReq->shouldReceive('isPost')->andReturn(false);

        $this->mockEvent->shouldReceive('getApplication')->never();

        static::assertNull($this->sut->validateCsrfToken($this->mockEvent));
    }

    public function testValidateCsrfTokenWitelisted(): void
    {
        $this->mockReq
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->never();
        $this->mockReq->shouldReceive('getUri->getPath')->andReturn('unit_whitelisted_path');

        $this->mockEvent->shouldReceive('getApplication')->once()->andReturn($this->mockApp);

        static::assertNull($this->sut->validateCsrfToken($this->mockEvent));
    }

    public function testValidateCsrfTokenEmptyPost(): void
    {
        $this->mockReq->shouldReceive('isPost')->andReturn(false);
        $this->mockReq->shouldReceive('getPost->count')->never();

        $this->mockEvent->shouldReceive('getApplication')->never();

        static::assertNull($this->sut->validateCsrfToken($this->mockEvent));
    }

    public function testValidateCsrfTokenValid(): void
    {
        $validator = new Csrf(['name' => 'security']);
        $hash = $validator->getHash();

        $mockParams = m::mock(\Laminas\Stdlib\Parameters::class)
            ->shouldReceive('count')->andReturn(1)
            ->getMock();

        $this->mockReq->shouldReceive('getUri->getPath')->andReturn('unit_NOT_whitelisted_path');
        $this->mockReq
            ->shouldReceive('isPost')->once()->andReturn(true)
            ->shouldReceive('getPost')->once()->withNoArgs()->andReturn($mockParams)
            ->shouldReceive('getPost')->once()->with('security')->andReturn($hash);

        $this->mockEvent->shouldReceive('getApplication')->once()->andReturn($this->mockApp);

        static::assertNull($this->sut->validateCsrfToken($this->mockEvent));
    }

    public function testValidateCsrfTokenNotValid(): void
    {
        $mockFlashHlp = m::mock(\Common\Service\Helper\FlashMessengerHelperService::class);
        $mockFlashHlp->shouldReceive('addErrorMessage')->once()->with('csrf-message');
        $this->mockSm->shouldReceive('get')->with('Helper\FlashMessenger')->andReturn($mockFlashHlp);

        $mockParams = m::mock(\Laminas\Stdlib\Parameters::class)
            ->shouldReceive('count')->andReturn(1)
            ->getMock();

        $this->mockReq->shouldReceive('getUri->getPath')->andReturn('unit_NOT_whitelisted_host');
        $this->mockReq
            ->shouldReceive('isPost')->once()->andReturn(true)
            ->shouldReceive('getPost')->once()->withNoArgs()->andReturn($mockParams)
            ->shouldReceive('getPost')->once()->with('security')->andReturn('NOT VALID HASH')
            ->shouldReceive('setMethod')->once()->with(\Laminas\Http\Request::METHOD_GET);

        $this->mockEvent->shouldReceive('getApplication')->once()->andReturn($this->mockApp);
        $this->mockEvent
            ->shouldReceive('getResponse->getHeaders->addHeaderLine')
            ->once()
            ->with('X-CSRF-error', '1');

        static::assertNull($this->sut->validateCsrfToken($this->mockEvent));
    }
}
