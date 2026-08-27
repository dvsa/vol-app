<?php

declare(strict_types=1);

namespace OlcsTest\Service\GovUkAccount;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\GovUkAccount\CallbackClaimStatus;
use Olcs\Service\GovUkAccount\CallbackReplayStore;
use PHPUnit\Framework\Attributes\Test;

final class CallbackReplayStoreTest extends MockeryTestCase
{
    private const string CODE = 'UmT5isA_NDSuUBWd_GSZ';
    private const string USER = '1234';
    private const string OTHER_USER = '5678';
    private const string URL = '/licence/672250/surrender/confirmation/';

    private function key(): string
    {
        return 'govuk-account-callback:' . hash('sha256', self::CODE);
    }

    private function stored(string $userId, ?string $url): string
    {
        return json_encode(['u' => $userId, 'r' => $url], JSON_THROW_ON_ERROR);
    }

    #[Test]
    public function aCodeNotSeenBeforeIsClaimedByThisRequest(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')
            ->once()
            ->with($this->key(), $this->stored(self::USER, null), ['nx', 'ex' => 60])
            ->andReturnTrue();

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::USER);

        $this->assertSame(CallbackClaimStatus::Claimed, $claim->status);
        $this->assertTrue($claim->ownsCode());
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function theSameUserReplayingAFinishedCallbackGetsTheStoredUrl(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->with($this->key())
            ->andReturn($this->stored(self::USER, self::URL));

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::USER);

        $this->assertSame(CallbackClaimStatus::ReplayComplete, $claim->status);
        $this->assertTrue($claim->isOwnReplay());
        $this->assertFalse($claim->ownsCode());
        $this->assertSame(self::URL, $claim->redirectUrl);
    }

    #[Test]
    public function theSameUserReplayingWhileTheFirstRequestRunsHasNoUrl(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->andReturn($this->stored(self::USER, null));

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::USER);

        $this->assertSame(CallbackClaimStatus::ReplayInFlight, $claim->status);
        $this->assertTrue($claim->isOwnReplay());
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function aDifferentUserNeverReceivesTheStoredUrl(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->andReturn($this->stored(self::USER, self::URL));

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::OTHER_USER);

        $this->assertSame(CallbackClaimStatus::ForeignReplay, $claim->status);
        $this->assertFalse($claim->isOwnReplay());
        $this->assertFalse($claim->ownsCode());
        $this->assertNull($claim->redirectUrl);
    }

    #[Test]
    public function anUnreadableEntryIsTreatedAsForeign(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->andReturn('not-json');

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::USER);

        $this->assertSame(CallbackClaimStatus::ForeignReplay, $claim->status);
    }

    #[Test]
    public function aKeyThatExpiredBetweenClaimAndReadIsTreatedAsUnclaimed(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andReturnFalse();
        $redis->shouldReceive('get')->once()->andReturnFalse();

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::USER);

        $this->assertSame(CallbackClaimStatus::Claimed, $claim->status);
    }

    #[Test]
    public function anUnavailableCacheFallsBackToProcessingTheCallback(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andThrow(new \RedisException('connection refused'));

        $claim = (new CallbackReplayStore($redis))->claim(self::CODE, self::USER);

        $this->assertSame(CallbackClaimStatus::Claimed, $claim->status);
    }

    #[Test]
    public function aCallbackWithoutACodeOrUserNeverTouchesTheCache(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldNotReceive('set');
        $redis->shouldNotReceive('get');

        $store = new CallbackReplayStore($redis);

        $this->assertSame(CallbackClaimStatus::Claimed, $store->claim('', self::USER)->status);
        $this->assertSame(CallbackClaimStatus::Claimed, $store->claim(self::CODE, '')->status);
    }

    #[Test]
    public function recordingAnOutcomeStoresTheUrlAgainstTheCodeAndUser(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')
            ->once()
            ->with($this->key(), $this->stored(self::USER, self::URL), ['ex' => 60])
            ->andReturnTrue();

        (new CallbackReplayStore($redis))->recordOutcome(self::CODE, self::USER, self::URL);
    }

    #[Test]
    public function recordingAnOutcomeWithoutACodeOrUserNeverTouchesTheCache(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldNotReceive('set');

        $store = new CallbackReplayStore($redis);
        $store->recordOutcome('', self::USER, self::URL);
        $store->recordOutcome(self::CODE, '', self::URL);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function recordingAnOutcomeSwallowsCacheFailures(): void
    {
        $redis = m::mock(\Redis::class);
        $redis->shouldReceive('set')->once()->andThrow(new \RedisException('connection refused'));

        (new CallbackReplayStore($redis))->recordOutcome(self::CODE, self::USER, self::URL);

        $this->addToAssertionCount(1);
    }
}
