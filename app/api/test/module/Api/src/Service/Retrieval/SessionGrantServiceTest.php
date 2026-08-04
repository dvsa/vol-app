<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Retrieval;

use Dvsa\Olcs\Api\Service\Retrieval\SessionGrantService;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

final class SessionGrantServiceTest extends TestCase
{
    private const SECRET = 'test-secret-that-is-at-least-32-chars-long';

    private SessionGrantService $sut;

    protected function setUp(): void
    {
        $this->sut = new SessionGrantService(self::SECRET);
    }

    public function testShortSecretIsConstructableButUnusable(): void
    {
        // gate=none flows construct this but never use it, so construction must not throw.
        $svc = new SessionGrantService('too-short');
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');

        // Validation fails closed with no usable secret...
        self::assertFalse($svc->isValid('anything', 'link', $now));

        // ...and issuing a grant fails loudly (surfaces an OTP-flow misconfiguration).
        $this->expectException(\RuntimeException::class);
        $svc->issue('link', $now);
    }

    public function testIssuedGrantIsValidForItsLink(): void
    {
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');
        $grant = $this->sut->issue('link-token-abc', $now);

        self::assertTrue($this->sut->isValid($grant, 'link-token-abc', $now));
    }

    public function testGrantIsRejectedForADifferentLink(): void
    {
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');
        $grant = $this->sut->issue('link-token-abc', $now);

        // A grant earned for one link must never unlock another.
        self::assertFalse($this->sut->isValid($grant, 'different-link', $now));
    }

    public function testExpiredGrantIsRejected(): void
    {
        $issuedAt = new \DateTimeImmutable('2026-07-20 09:00:00');
        $grant = $this->sut->issue('link-token-abc', $issuedAt);

        $laterThanTtl = $issuedAt->modify('+' . (SessionGrantService::TTL_SECONDS + 1) . ' seconds');

        self::assertFalse($this->sut->isValid($grant, 'link-token-abc', $laterThanTtl));
    }

    public function testTamperedPayloadIsRejected(): void
    {
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');
        $grant = $this->sut->issue('link-token-abc', $now);

        [$payload, $sig] = explode('.', $grant);
        $forged = rtrim(strtr(base64_encode((string) json_encode(['t' => 'link-token-abc', 'exp' => PHP_INT_MAX])), '+/', '-_'), '=');

        self::assertFalse($this->sut->isValid($forged . '.' . $sig, 'link-token-abc', $now));
    }

    public function testGrantFromADifferentSecretIsRejected(): void
    {
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');
        $grant = (new SessionGrantService('another-secret-at-least-32-characters!!'))->issue('link-token-abc', $now);

        self::assertFalse($this->sut->isValid($grant, 'link-token-abc', $now));
    }

    public function testMalformedGrantIsRejected(): void
    {
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');

        self::assertFalse($this->sut->isValid('not-a-valid-grant', 'link-token-abc', $now));
    }
}
