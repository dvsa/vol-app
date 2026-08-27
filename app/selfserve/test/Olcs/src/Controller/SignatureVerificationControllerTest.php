<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse;
use Laminas\Http\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\SignatureVerificationController as Sut;
use Olcs\Service\GovUkAccount\CallbackClaim;
use Olcs\Service\GovUkAccount\CallbackReplayStore;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

final class SignatureVerificationControllerTest extends MockeryTestCase
{
    private const string CODE = 'UmT5isA_NDSuUBWd_GSZ';
    private const string SUCCESS = '/licence/672250/surrender/confirmation/';
    private const string FAILURE = '/licence/672250/surrender/declaration/sign-with-external/';

    private $sut;

    private $store;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->store = m::mock(CallbackReplayStore::class);

        $property = (new ReflectionClass(Sut::class))->getProperty('replayStore');
        $property->setValue($this->sut, $this->store);

        $request = m::mock();
        $request->shouldReceive('getQuery')->with('code')->andReturn(self::CODE);
        $request->shouldReceive('getQuery')->with('state')->andReturn('a.b.c');
        $request->shouldReceive('getQuery')->with('error')->andReturnNull();
        $request->shouldReceive('getQuery')->with('errorDescription')->andReturnNull();
        $this->sut->shouldReceive('getRequest')->andReturn($request);
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
    public function aReplayOfAFinishedCallbackRedirectsWithoutCallingTheApi(): void
    {
        $this->store->shouldReceive('claim')
            ->once()
            ->with(self::CODE)
            ->andReturn(CallbackClaim::replayOf(self::SUCCESS));
        $this->sut->shouldNotReceive('handleCommand');
        $this->expectRedirectTo(self::SUCCESS);

        $this->sut->indexAction();
    }

    #[Test]
    public function aFirstCallbackProcessesNormallyAndRecordsWhereItSentTheUser(): void
    {
        $this->store->shouldReceive('claim')->once()->andReturn(CallbackClaim::claimed());
        $this->apiReturns([
            'redirect_url' => self::SUCCESS,
            'redirect_url_on_error' => self::FAILURE,
        ]);
        $this->store->shouldReceive('recordOutcome')->once()->with(self::CODE, self::SUCCESS);
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

        $this->store->shouldReceive('recordOutcome')->once()->with(self::CODE, self::FAILURE);
        $this->expectRedirectTo(self::FAILURE);

        $this->sut->indexAction();
    }

    #[Test]
    public function aReplayWhoseExchangeFailsGoesToTheSuccessUrlAndRecordsNothing(): void
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
        $request = m::mock();
        $request->shouldReceive('getQuery')->with('code')->andReturn(['injected']);
        $request->shouldReceive('getQuery')->with('state')->andReturn('a.b.c');
        $request->shouldReceive('getQuery')->with('error')->andReturnNull();
        $request->shouldReceive('getQuery')->with('errorDescription')->andReturnNull();

        $sut = m::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
        (new ReflectionClass(Sut::class))->getProperty('replayStore')->setValue($sut, $this->store);
        $sut->shouldReceive('getRequest')->andReturn($request);

        // '' rather than the string 'Array', which would collide across requests.
        $this->store->shouldReceive('claim')->once()->with('')->andReturn(CallbackClaim::claimed());

        $response = m::mock('stdClass');
        $response->shouldReceive('getResult')->andReturn(['flags' => ['redirect_url' => self::SUCCESS]]);
        $response->shouldReceive('getStatusCode')->andReturn(201);
        $sut->shouldReceive('handleCommand')->andReturn($response);

        $this->store->shouldReceive('recordOutcome')->once()->with('', self::SUCCESS);

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
