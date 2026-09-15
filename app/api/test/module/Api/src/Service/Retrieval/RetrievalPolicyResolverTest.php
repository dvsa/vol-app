<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Retrieval;

use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicy;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicyResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

final class RetrievalPolicyResolverTest extends TestCase
{
    public function testResolvesConfiguredPublicFlow(): void
    {
        $resolver = new RetrievalPolicyResolver([
            'publication' => ['gate' => 'none', 'expiry' => 'P42D'],
        ]);

        $policy = $resolver->resolve('publication');

        self::assertSame(RetrievalPolicy::GATE_NONE, $policy->gate);
        self::assertFalse($policy->requiresOtp());
        self::assertSame(42 * 86400, $policy->expirySeconds);
    }

    public function testResolvesConfiguredOtpFlow(): void
    {
        $resolver = new RetrievalPolicyResolver([
            'financial-evidence' => ['gate' => 'otp', 'expiry' => 'PT72H'],
        ]);

        $policy = $resolver->resolve('financial-evidence');

        self::assertTrue($policy->requiresOtp());
        self::assertSame(72 * 3600, $policy->expirySeconds);
    }

    public function testExpiryMayBeGivenInSeconds(): void
    {
        $resolver = new RetrievalPolicyResolver(['x' => ['gate' => 'none', 'expiry' => 3600]]);

        self::assertSame(3600, $resolver->resolve('x')->expirySeconds);
    }

    public function testUnknownFlowFailsSecureToOtp(): void
    {
        $resolver = new RetrievalPolicyResolver([]);

        $policy = $resolver->resolve('never-configured');

        // Fail secure: no config must never mean "no gate".
        self::assertTrue($policy->requiresOtp());
        self::assertSame(259200, $policy->expirySeconds);
    }

    public function testGateDefaultsToOtpWhenOmitted(): void
    {
        $resolver = new RetrievalPolicyResolver(['x' => ['expiry' => 'P1D']]);

        self::assertTrue($resolver->resolve('x')->requiresOtp());
    }

    public function testExpiryDefaultsToSixWeeksWhenOmitted(): void
    {
        $resolver = new RetrievalPolicyResolver(['x' => ['gate' => 'none']]);

        self::assertSame(42 * 86400, $resolver->resolve('x')->expirySeconds);
    }

    public function testInvalidGateThrows(): void
    {
        $resolver = new RetrievalPolicyResolver(['x' => ['gate' => 'sometimes']]);

        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve('x');
    }

    public function testInvalidDurationThrows(): void
    {
        $resolver = new RetrievalPolicyResolver(['x' => ['gate' => 'none', 'expiry' => 'not-a-duration']]);

        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve('x');
    }

    public function testExpiresFromAddsWindow(): void
    {
        $policy = new RetrievalPolicy(RetrievalPolicy::GATE_NONE, 3600);
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');

        self::assertSame('2026-07-20 10:00:00', $policy->expiresFrom($now)->format('Y-m-d H:i:s'));
    }
}
