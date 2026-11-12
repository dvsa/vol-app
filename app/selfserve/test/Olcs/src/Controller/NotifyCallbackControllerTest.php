<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Exception as CqrsException;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException as CqrsNotFoundException;
use Common\Service\Cqrs\Response as CqrsResponse;
use Dvsa\Olcs\Transfer\Command\Notify\UpdateDeliveryStatus;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\NotifyCallbackController;
use Olcs\Logging\Log\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class NotifyCallbackControllerTest extends MockeryTestCase
{
    private const string TOKEN = 'a-long-secret-token';
    private const string BODY = '{"id": "740e5834-3a29-46b4-9a6f-16142fde533a", "status": "delivered", "completed_at": "2026-11-11T12:00:00.000000Z", "sent_at": "2026-11-11T11:59:00.000000Z", "reference": "dev-sms-555"}';

    private $commandSender;
    private $logger;

    public function setUp(): void
    {
        $this->commandSender = m::mock(CommandSender::class);
        $this->logger = m::mock(LoggerInterface::class);
        Logger::setLogger($this->logger);
    }

    public function tearDown(): void
    {
        Logger::setLogger(new NullLogger());
        parent::tearDown();
    }

    private function dispatch(?string $authorization, string $body = self::BODY): Response
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->setContent($body);

        if ($authorization !== null) {
            $request->getHeaders()->addHeaderLine('Authorization', $authorization);
        }

        $event = new MvcEvent();
        $event->setRouteMatch(new RouteMatch(['action' => 'index']));

        $sut = new NotifyCallbackController($this->commandSender);
        $sut->setEvent($event);

        return $sut->dispatch($request, new Response());
    }

    private function expectSend(): m\CompositeExpectation
    {
        return $this->commandSender->shouldReceive('send')
            ->once()
            ->with(m::on(fn($command) => $command instanceof UpdateDeliveryStatus
                && $command->getToken() === self::TOKEN
                && $command->getId() === '740e5834-3a29-46b4-9a6f-16142fde533a'
                && $command->getStatus() === 'delivered'
                && $command->getCompletedAt() === '2026-11-11T12:00:00.000000Z'
                && $command->getSentAt() === '2026-11-11T11:59:00.000000Z'
                && $command->getReference() === 'dev-sms-555'));
    }

    private function apiResponse(bool $ok)
    {
        $response = m::mock(CqrsResponse::class);
        $response->shouldReceive('isOk')->once()->andReturn($ok);

        return $response;
    }

    public function testCorrectTokenSendsCommand(): void
    {
        $this->expectSend()->andReturn($this->apiResponse(true));

        $this->assertSame(200, $this->dispatch('Bearer ' . self::TOKEN)->getStatusCode());
    }

    #[DataProvider('apiExceptionProvider')]
    public function testApiExceptionIs500(\Throwable $exception): void
    {
        $this->expectSend()->andThrow($exception);
        $this->logger->shouldReceive('warning')->once()->with(m::type('string'), m::type('array'));

        $this->assertSame(500, $this->dispatch('Bearer ' . self::TOKEN)->getStatusCode());
    }

    public static function apiExceptionProvider(): array
    {
        return [
            'api error' => [new CqrsException('boom')],
            'not found' => [new CqrsNotFoundException('404')],
            'conflict' => [new ResourceConflictException('409')],
        ];
    }

    public function testApiRejectingTheTokenIs401(): void
    {
        $this->expectSend()->andThrow(new AccessDeniedException('403'));
        $this->logger->shouldReceive('warning')->once()->with(m::type('string'), m::type('array'));

        $this->assertSame(401, $this->dispatch('Bearer ' . self::TOKEN)->getStatusCode());
    }

    public function testApiClientErrorIs400(): void
    {
        $this->expectSend()->andReturn($this->apiResponse(false));

        $this->assertSame(400, $this->dispatch('Bearer ' . self::TOKEN)->getStatusCode());
    }

    #[DataProvider('unauthorisedProvider')]
    public function testMissingBearerHeaderIs401(?string $authorization): void
    {
        $this->commandSender->shouldReceive('send')->never();
        $this->logger->shouldReceive('warning')->once()->with(m::type('string'), m::type('array'));

        $this->assertSame(401, $this->dispatch($authorization)->getStatusCode());
    }

    public static function unauthorisedProvider(): array
    {
        return [
            'no header' => [null],
            'not a bearer header' => [self::TOKEN],
            'empty bearer' => ['Bearer '],
        ];
    }

    public function testBadJson(): void
    {
        $this->commandSender->shouldReceive('send')->never();

        $this->assertSame(400, $this->dispatch('Bearer ' . self::TOKEN, 'not json')->getStatusCode());
    }
}
