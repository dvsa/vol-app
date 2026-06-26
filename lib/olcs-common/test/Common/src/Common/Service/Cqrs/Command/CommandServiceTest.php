<?php

namespace CommonTest\Common\Service\Cqrs\Command;

use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Exception;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\CommandContainer;
use Dvsa\Olcs\Transfer\Command\CommandContainerInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserLastLoginAt;
use Laminas\Http\Client\Exception\RuntimeException;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Http\Response as HttpResponse;
use Laminas\Router\Exception\RuntimeException as RouterRuntimeException;
use Laminas\Session\Container;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Cqrs\Command\CommandService
 */
class CommandServiceTest extends MockeryTestCase
{
    public $mockContainer;
    public const ROUTE_NAME = 'backend/aaa/bbb';

    public const METHOD = 'POST';

    /** @var  CommandService */
    private $sut;

    /** @var  m\MockInterface */
    private $mockDto;

    /** @var  m\MockInterface | CommandContainerInterface */
    private $mockCmd;

    /** @var  m\MockInterface | \Laminas\Router\RouteInterface */
    private $mockRouter;

    /** @var  m\MockInterface | \Laminas\Http\Client */
    private $mockClient;

    /** @var  m\MockInterface | \Laminas\Http\Request */
    private $mockRequest;

    /** @var  m\MockInterface | \Common\Service\Helper\FlashMessengerHelperService */
    private $mockFlashMsgr;


    #[\Override]
    protected function setUp(): void
    {
        $this->mockDto = m::mock(CommandInterface::class);
        $this->mockDto->shouldReceive('getArrayCopy')->atMost(1)->andReturn([]);

        $this->mockCmd = m::mock(CommandContainer::class);
        $this->mockCmd
            ->shouldReceive('getRouteName')->atMost(1)->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->atMost(1)->andReturn(self::METHOD)
            ->shouldReceive('getDto')->atMost(1)->andReturn($this->mockDto);

        $this->mockRouter = m::mock(\Laminas\Router\RouteInterface::class)->makePartial();
        $this->mockClient = m::mock(\Laminas\Http\Client::class)->makePartial();
        $this->mockRequest = m::mock(\Laminas\Http\Request::class)->makePartial();
        $this->mockFlashMsgr = m::mock(\Common\Service\Helper\FlashMessengerHelperService::class);
        $this->mockContainer = m::mock(Container::class);
        $this->mockContainer->allows('offsetGet')->andReturn([])->byDefault();

        $this->sut = new CommandService(
            $this->mockRouter,
            $this->mockClient,
            $this->mockRequest,
            true,
            $this->mockFlashMsgr,
            $this->mockContainer
        );
    }

    public function testSend404ErrorWithRoute(): void
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->andThrow(new RouterRuntimeException('err_message'));
        $this->mockFlashMsgr->shouldReceive('addErrorMessage')->with('DEBUG: err_message');

        $this->expectException(Exception::class);
        $this->sut->send($this->mockCmd);
    }

    public function testSend422(): void
    {
        $this->mockCmd
            ->shouldReceive('isValid')->once()->andReturn(false)
            ->shouldReceive('getMessages')->once()->andReturn(['EXPECT_MESSAGES']);

        $this->mockFlashMsgr->shouldReceive('addErrorMessage')->once()->andReturn('EXPECT_MESSAGES');

        $actual = $this->sut->send($this->mockCmd);

        $this->assertInvalidResponse($actual, 'EXPECT_MESSAGES', HttpResponse::STATUS_CODE_422);
    }

    public function testSend404(): void
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->atLeast()->times(1)->andReturn(HttpResponse::STATUS_CODE_404);

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->expectException(
            Exception\NotFoundException::class
        );
        $this->sut->send($this->mockCmd);
    }

    public function testSend403(): void
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->atLeast()->times(1)->andReturn(HttpResponse::STATUS_CODE_403);
        $mockResp->shouldReceive('getBody')->with()->once()->andReturn('HTTP BODY');

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->expectException(Exception\AccessDeniedException::class);
        $this->sut->send($this->mockCmd);
    }

    public function testSend500(): void
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->atLeast()->times(1)->andReturn(HttpResponse::STATUS_CODE_500);
        $mockResp->shouldReceive('getBody')->with()->once()->andReturn('HTTP BODY');

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->expectException(Exception::class);
        $this->sut->send($this->mockCmd);
    }

    public function testSendOtherException(): void
    {
        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andThrow(RuntimeException::class, 'ERROR');

        $this->expectException(Exception::class);
        $this->sut->send($this->mockCmd);
    }

    public function testSend409(): void
    {
        $this->expectException(ResourceConflictException::class);

        $this->mockCmd->shouldReceive('isValid')->once()->andReturn(true);
        $this->mockRouter->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $mockResp = m::mock(HttpResponse::class);
        $mockResp->shouldReceive('getStatusCode')->once()->andReturn(HttpResponse::STATUS_CODE_409);

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn()
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $this->sut->send($this->mockCmd);
    }

    public function testSendFile(): void
    {
        //  mock command
        $dtoData = [
            'xfile' => new FileContent('unit_fileName', 'unit_mime'),
        ];
        $mockDto = m::mock(LoggerOmitContentInterface::class);
        $mockDto->shouldReceive('getArrayCopy')->once()->andReturn($dtoData);

        $mockCmd = m::mock(CommandContainer::class)
            ->shouldReceive('getRouteName')->once()->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->once()->andReturn(self::METHOD)
            ->shouldReceive('getDto')->times(3)->andReturn($mockDto)
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->getMock();

        //  mock
        $this->mockRouter
            ->shouldReceive('assemble')->once()->andReturnUsing(
                static function ($data, $path) use ($dtoData) {
                    static::assertEquals($dtoData, $data);
                    static::assertEquals(['name' => 'api/backend/api/aaa/bbb/' . self::METHOD], $path);
                    return 'unit_uri';
                }
            );

        $headers = new Headers();
        $headers->addHeaderLine('Content-type: should be removed');
        $headers->addHeaderLine('x-header: test for headers');

        $this->mockRequest->setHeaders($headers);
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('unit_uri')
            ->shouldReceive('setMethod')->once()->with(self::METHOD);

        $mockAdapter = m::mock(LoggerOmitContentInterface::class)
            ->shouldReceive('getShouldLogData')->once()->andReturn('shouldLog')
            ->shouldReceive('setShouldLogData')->once()->with(false)
            ->shouldReceive('setShouldLogData')->once()->with('shouldLog')
            ->getMock();

        $mockResp = m::mock(HttpResponse::class)->makePartial()
            ->shouldReceive('getStatusCode')->andReturn(HttpResponse::STATUS_CODE_200)
            ->shouldReceive('getBody')->once()->andReturn('{"key":"EXPECTED"}')
            ->getMock();

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('setFileUpload')->once()->with('unit_fileName', 'xfile', null, 'unit_mime')
            ->shouldReceive('send')->once()->andReturn($mockResp);

        //  call & check
        $actual = $this->sut->send($mockCmd);

        static::assertEquals(1, $this->mockClient->getRequest()->getHeaders()->count());
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertEquals(['key' => 'EXPECTED'], $actual->getResult());
    }

    public function testWhenCommandHasSecureTokenThenTokenIsApplied(): void
    {
        //  mock command
        $dtoData = [
            'secureToken' => 'exampleSecureToken',
        ];
        $dto = UpdateUserLastLoginAt::create($dtoData);

        $mockCmd = m::mock(CommandContainer::class)
            ->shouldReceive('getRouteName')->once()->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->once()->andReturn(self::METHOD)
            ->shouldReceive('getDto')->times(3)->andReturn($dto)
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->getMock();

        //  mock
        $this->mockRouter
            ->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $headers = new Headers();

        $this->mockRequest->setHeaders($headers);
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('unit_uri')
            ->shouldReceive('setMethod')->once()->with(self::METHOD);

        $mockAdapter = m::mock(LoggerOmitContentInterface::class);

        $mockResp = m::mock(HttpResponse::class)->makePartial()
            ->shouldReceive('getStatusCode')->andReturn(HttpResponse::STATUS_CODE_200)
            ->shouldReceive('getBody')->once()->andReturn('{"key":"EXPECTED"}')
            ->getMock();

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $actual = $this->sut->send($mockCmd);

        static::assertEquals(1, $this->mockClient->getRequest()->getHeaders()->count());
        static::assertTrue($this->mockClient->getRequest()->getHeaders()->has('Cookie'));
        static::assertEquals(
            'secureToken=exampleSecureToken',
            $this->mockClient->getRequest()->getCookie()->getFieldValue()
        );
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertEquals(['key' => 'EXPECTED'], $actual->getResult());
    }

    public function testWhenCommandHasSecureTokenThenTokenIsOverridden(): void
    {
        //  mock command
        $dtoData = [
            'secureToken' => 'exampleSecureToken',
        ];
        $dto = UpdateUserLastLoginAt::create($dtoData);

        $mockCmd = m::mock(CommandContainer::class)
            ->shouldReceive('getRouteName')->once()->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->once()->andReturn(self::METHOD)
            ->shouldReceive('getDto')->times(3)->andReturn($dto)
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->getMock();

        //  mock
        $this->mockRouter
            ->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $headers = new Headers();
        $headers->addHeader(new Cookie(['secureToken' => 'someValueToBeOverridden']));

        $this->mockRequest->setHeaders($headers);
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('unit_uri')
            ->shouldReceive('setMethod')->once()->with(self::METHOD);

        $mockAdapter = m::mock(LoggerOmitContentInterface::class);

        $mockResp = m::mock(HttpResponse::class)->makePartial()
            ->shouldReceive('getStatusCode')->andReturn(HttpResponse::STATUS_CODE_200)
            ->shouldReceive('getBody')->once()->andReturn('{"key":"EXPECTED"}')
            ->getMock();

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $actual = $this->sut->send($mockCmd);

        static::assertEquals(1, $this->mockClient->getRequest()->getHeaders()->count());
        static::assertTrue($this->mockClient->getRequest()->getHeaders()->has('Cookie'));
        static::assertEquals(
            'secureToken=exampleSecureToken',
            $this->mockClient->getRequest()->getCookie()->getFieldValue()
        );
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertEquals(['key' => 'EXPECTED'], $actual->getResult());
    }

    public function testWhenCommandHasNoSecureTokenThenTokenIsLeftIntact(): void
    {
        //  mock command
        $dtoData = [];
        $dto = UpdateUserLastLoginAt::create($dtoData);

        $mockCmd = m::mock(CommandContainer::class)
            ->shouldReceive('getRouteName')->once()->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->once()->andReturn(self::METHOD)
            ->shouldReceive('getDto')->times(3)->andReturn($dto)
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->getMock();

        //  mock
        $this->mockRouter
            ->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $headers = new Headers();
        $headers->addHeader(new Cookie(['secureToken' => 'theDefaultValue']));

        $this->mockRequest->setHeaders($headers);
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('unit_uri')
            ->shouldReceive('setMethod')->once()->with(self::METHOD);

        $mockAdapter = m::mock(LoggerOmitContentInterface::class);

        $mockResp = m::mock(HttpResponse::class)->makePartial()
            ->shouldReceive('getStatusCode')->andReturn(HttpResponse::STATUS_CODE_200)
            ->shouldReceive('getBody')->once()->andReturn('{"key":"EXPECTED"}')
            ->getMock();

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $actual = $this->sut->send($mockCmd);

        static::assertEquals(1, $this->mockClient->getRequest()->getHeaders()->count());
        static::assertTrue($this->mockClient->getRequest()->getHeaders()->has('Cookie'));
        static::assertEquals(
            'secureToken=theDefaultValue',
            $this->mockClient->getRequest()->getCookie()->getFieldValue()
        );
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertEquals(['key' => 'EXPECTED'], $actual->getResult());
    }

    /**
     * @psalm-param 'EXPECT_MESSAGES' $message
     * @psalm-param 422 $statusCode
     */
    private function assertInvalidResponse(CqrsResponse $actual, string $message, int $statusCode): void
    {
        static::assertInstanceOf(CqrsResponse::class, $actual);
        static::assertStringStartsWith($message, current($actual->getResult()['messages']));
        static::assertEquals($statusCode, $actual->getHttpResponse()->getStatusCode());
    }

    public function testAddAuthorizationHeader(): void
    {
        //  mock command
        $dtoData = [];
        $dto = UpdateUserLastLoginAt::create($dtoData);

        $mockCmd = m::mock(CommandContainer::class)
            ->shouldReceive('getRouteName')->once()->andReturn(self::ROUTE_NAME)
            ->shouldReceive('getMethod')->once()->andReturn(self::METHOD)
            ->shouldReceive('getDto')->times(3)->andReturn($dto)
            ->shouldReceive('isValid')->once()->andReturn(true)
            ->getMock();

        //  mock
        $this->mockRouter
            ->shouldReceive('assemble')->once()->andReturn('unit_uri');

        $this->mockContainer
            ->shouldReceive('offsetGet')
            ->with('storage')
            ->andReturn(['AccessToken' => 'access_token']);

        $headers = new Headers();
        $this->mockRequest->setHeaders($headers);
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('unit_uri')
            ->shouldReceive('setMethod')->once()->with(self::METHOD)
            ->shouldReceive('getHeaders')->once()->andReturn($headers);

        $mockAdapter = m::mock(LoggerOmitContentInterface::class);

        $mockResp = m::mock(HttpResponse::class)->makePartial()
            ->shouldReceive('getStatusCode')->andReturn(HttpResponse::STATUS_CODE_200)
            ->shouldReceive('getBody')->once()->andReturn('{"key":"EXPECTED"}')
            ->getMock();

        $this->mockClient
            ->shouldReceive('getAdapter')->once()->andReturn($mockAdapter)
            ->shouldReceive('send')->once()->andReturn($mockResp);

        $actual = $this->sut->send($mockCmd);

        static::assertInstanceOf(CqrsResponse::class, $actual);
        $this->assertArrayHasKey('Authorization', $headers->toArray());
        $this->assertSame('Bearer access_token', $headers->get('Authorization')->getFieldValue());
    }
}
