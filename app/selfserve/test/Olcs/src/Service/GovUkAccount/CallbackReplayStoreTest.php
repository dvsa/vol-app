<?php

declare(strict_types=1);

namespace OlcsTest\Service\GovUkAccount;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\GovUkAccount\CallbackReplayStore;
use PHPUnit\Framework\Attributes\Test;

final class CallbackReplayStoreTest extends MockeryTestCase
{
    private const CODE = 'UmT5isA_NDSuUBWd_GSZ';

    private function key(): string
    {
        return 'govuk-account-callback:' . hash('sha256', self::CODE);
    }

    #[Test]
    public function aCodeNotSeenBeforeIsClaimedByThisRequest(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')
            ->once()
            ->with($this->key(), 'in-progress', ['nx', 'ex' => 60])
            ->andReturnTrue();

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE);

        $this->assertFalse($claim->isReplay);
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function aReplayOfAFinishedCallbackReturnsTheStoredRedirectUrl(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')
            ->once()
            ->with($this->key())
            ->andReturn('/licence/672250/surrender/confirmation/');

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE);

        $this->assertTrue($claim->isReplay);
        $this->assertSame('/licence/672250/surrender/confirmation/', $claim->redirectUrl);
    }

    #[Test]
    public function aReplayArrivingWhileTheFirstRequestRunsHasNoRedirectUrl(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->andReturn('in-progress');

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE);

        $this->assertTrue($claim->isReplay);
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function aMissingKeyOnReadIsTreatedAsStillInFlight(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->andReturnFalse();

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE);

        $this->assertTrue($claim->isReplay);
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function anUnavailableCacheFallsBackToProcessingTheCallback(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andThrow(new \RedisException('connection refused'));

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE);

        $this->assertFalse($claim->isReplay);
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function aCallbackWithoutACodeNeverTouchesTheCache(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldNotReceive('set');
        $redis->shouldNotReceive('get');

        $claim = (new CallbackReplayStore($redis))->claim('');

        $this->assertFalse($claim->isReplay);
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function recordingAnOutcomeStoresTheRedirectUrlAgainstTheCode(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')
            ->once()
            ->with($this->key(), '/application/1593818/undertakings/', ['ex' => 60])
            ->andReturnTrue();

        (new CallbackReplayStore($redis))
            ->recordOutcome(self::CODE, '/application/1593818/undertakings/');
    }

    #[Test]
    public function recordingAnOutcomeWithoutACodeNeverTouchesTheCache(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldNotReceive('set');

        (new CallbackReplayStore($redis))->recordOutcome('', '/anywhere/');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function recordingAnOutcomeSwallowsCacheFailures(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andThrow(new \RedisException('connection refused'));

        (new CallbackReplayStore($redis))->recordOutcome(self::CODE, '/anywhere/');

        $this->addToAssertionCount(1);
    }
}
