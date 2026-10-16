<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse;
use Laminas\Http\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\SignatureVerificationController as Sut;
use Olcs\Logging\Log\Logger;
use Olcs\Service\GovUkAccount\CallbackClaim;
use Olcs\Service\GovUkAccount\CallbackReplayStore;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;

final class SignatureVerificationControllerTest extends MockeryTestCase
{
    private const string CODE = 'UmT5isA_NDSuUBWd_GSZ';
    private const string USER = '1234';
    private const string SUCCESS = '/licence/672250/surrender/confirmation/';
    private const string FAILURE = '/licence/672250/surrender/declaration/sign-with-external/';

    private $sut;

    private $store;

    private $logger;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->store = m::mock(CallbackReplayStore::class);

        (new ReflectionClass(Sut::class))->getProperty('replayStore')->setValue($this->sut, $this->store);

        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        Logger::setLogger($this->logger);

        $this->setUpRequest(self::CODE);
    }

    #[\Override]
    public function tearDown(): void
    {
        Logger::setLogger(new NullLogger());
        parent::tearDown();
    }

    private function setUpRequest(mixed $code): void
    {
        $request = m::mock();
        $request->shouldReceive('getQuery')->with('code')->andReturn($code);
        $request->shouldReceive('getQuery')->with('state')->andReturn('a.b.c');
        $request->shouldReceive('getQuery')->with('error')->andReturnNull();
        $request->shouldReceive('getQuery')->with('errorDescription')->andReturnNull();
        $this->sut->shouldReceive('getRequest')->andReturn($request);

        $currentUser = m::mock();
        $currentUser->shouldReceive('getUserData')->andReturn(['id' => self::USER]);
        $this->sut->shouldReceive('currentUser')->andReturn($currentUser);
    }

    private function apiReturns(array $flags): void
    {
        $response = m::mock('stdClass');
        $response->shouldReceive('getResult')->andReturn(['flags' => $flags]);
        $response->shouldReceive('getStatusCode')->andReturn(201);
        $this->sut->shouldReceive('handleCommand')
            ->with(m::type(ProcessAuthResponse::class))
            ->andReturn($response);
    }

    private function expectRedirectTo(string $url): void
    {
        $redirect = m::mock();
        $redirect->shouldReceive('toUrl')->once()->with($url)->andReturn(new Response());
        $this->sut->shouldReceive('redirect')->andReturn($redirect);
    }

    #[Test]
    public function anOwnReplayRedirectsWithoutCallingTheApiAndIsLoggedAtWarning(): void
    {
        $this->store->shouldReceive('claim')->once()->with(self::CODE, self::USER)
            ->andReturn(CallbackClaim::replayComplete(self::SUCCESS));
        $this->sut->shouldNotReceive('handleCommand');
        $this->logger->shouldReceive('warning')->once()
            ->with('GOV.UK One Login callback replayed; reusing the first outcome', m::type('array'));
        $this->logger->shouldNotReceive('error');
        $this->expectRedirectTo(self::SUCCESS);

        $this->sut->indexAction();
    }

    #[Test]
    public function aForeignReplayIsLoggedAtErrorAndProcessedAsNormal(): void
    {
        $this->store->shouldReceive('claim')->once()->andReturn(CallbackClaim::foreignReplay());
        $this->logger->shouldReceive('error')->once()
            ->with('GOV.UK One Login authorisation code replayed by a different user', m::type('array'));

        $this->apiReturns([
            'redirect_url' => self::SUCCESS,
            'redirect_url_on_error' => self::FAILURE,
            'error' => 'Code 400 : Request returned non-200 status code',
        ]);

        $container = m::mock();
        $container->shouldReceive('offsetSet')->once()->with('govUkAccountError', true);
        $flash = m::mock();
        $flash->shouldReceive('getContainer')->andReturn($container);
        $this->sut->shouldReceive('flashMessenger')->andReturn($flash);

        // Must never overwrite the real owner's stored destination.
        $this->store->shouldNotReceive('recordOutcome');
        $this->expectRedirectTo(self::FAILURE);

        $this->sut->indexAction();
    }

    #[Test]
    public function aFirstCallbackProcessesNormallyAndRecordsAgainstTheUser(): void
    {
        $this->store->shouldReceive('claim')->once()->andReturn(CallbackClaim::claimed());
        $this->apiReturns([
            'redirect_url' => self::SUCCESS,
            'redirect_url_on_error' => self::FAILURE,
        ]);
        $this->store->shouldReceive('recordOutcome')->once()->with(self::CODE, self::USER, self::SUCCESS);
        $this->logger->shouldNotReceive('warning');
        $this->logger->shouldNotReceive('error');
        $this->expectRedirectTo(self::SUCCESS);

        $this->sut->indexAction();
    }

    #[Test]
    public function aGenuineFailureOnAFirstCallbackStillGoesToTheErrorPage(): void
    {
        $this->store->shouldReceive('claim')->once()->andReturn(CallbackClaim::claimed());
        $this->apiReturns([
            'redirect_url' => self::SUCCESS,
            'redirect_url_on_error' => self::FAILURE,
            'error' => 'Code 400 : Request returned non-200 status code',
        ]);

        $container = m::mock();
        $container->shouldReceive('offsetSet')->once()->with('govUkAccountError', true);
        $flash = m::mock();
        $flash->shouldReceive('getContainer')->andReturn($container);
        $this->sut->shouldReceive('flashMessenger')->andReturn($flash);

        $this->store->shouldReceive('recordOutcome')->once()->with(self::CODE, self::USER, self::FAILURE);
        $this->expectRedirectTo(self::FAILURE);

        $this->sut->indexAction();
    }

    #[Test]
    public function anOwnReplayWhoseExchangeFailsGoesToTheSuccessUrlAndRecordsNothing(): void
    {
        $this->store->shouldReceive('claim')->once()->andReturn(CallbackClaim::replayInFlight());
        $this->apiReturns([
            'redirect_url' => self::SUCCESS,
            'redirect_url_on_error' => self::FAILURE,
            'error' => 'Code 400 : Request returned non-200 status code',
        ]);
        $this->sut->shouldNotReceive('flashMessenger');
        $this->store->shouldNotReceive('recordOutcome');
        $this->expectRedirectTo(self::SUCCESS);

        $this->sut->indexAction();
    }

    #[Test]
    public function anArrayCodeParameterIsNotUsedAsACacheKey(): void
    {
        $sut = m::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
        (new ReflectionClass(Sut::class))->getProperty('replayStore')->setValue($sut, $this->store);

        $request = m::mock();
        $request->shouldReceive('getQuery')->with('code')->andReturn(['injected']);
        $request->shouldReceive('getQuery')->with('state')->andReturn('a.b.c');
        $request->shouldReceive('getQuery')->with('error')->andReturnNull();
        $request->shouldReceive('getQuery')->with('errorDescription')->andReturnNull();
        $sut->shouldReceive('getRequest')->andReturn($request);

        $currentUser = m::mock();
        $currentUser->shouldReceive('getUserData')->andReturn(['id' => self::USER]);
        $sut->shouldReceive('currentUser')->andReturn($currentUser);

        // '' rather than the string 'Array', which would collide across requests.
        $this->store->shouldReceive('claim')->once()->with('', self::USER)
            ->andReturn(CallbackClaim::claimed());

        $response = m::mock('stdClass');
        $response->shouldReceive('getResult')->andReturn(['flags' => ['redirect_url' => self::SUCCESS]]);
        $response->shouldReceive('getStatusCode')->andReturn(201);
        $sut->shouldReceive('handleCommand')->andReturn($response);

        $this->store->shouldReceive('recordOutcome')->once()->with('', self::USER, self::SUCCESS);

        $redirect = m::mock();
        $redirect->shouldReceive('toUrl')->once()->with(self::SUCCESS)->andReturn(new Response());
        $sut->shouldReceive('redirect')->andReturn($redirect);

        $sut->indexAction();
    }

    #[Test]
    public function aResponseWithNoRedirectUrlStillThrows(): void
    {
        $this->store->shouldReceive('claim')->once()->andReturn(CallbackClaim::claimed());
        $this->apiReturns(['redirect_url' => null]);

        $this->expectException(\Exception::class);

        $this->sut->indexAction();
    }
}
