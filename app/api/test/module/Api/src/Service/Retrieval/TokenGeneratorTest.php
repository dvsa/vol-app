<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Retrieval;

use Dvsa\Olcs\Api\Service\Retrieval\TokenGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

final class TokenGeneratorTest extends TestCase
{
    private TokenGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new TokenGenerator();
    }

    public function testGeneratesUrlSafeToken(): void
    {
        $token = $this->sut->generate();

        // Only unreserved URL characters — safe to drop straight into a path segment.
        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $token);
    }

    public function testDefaultTokenIs43Characters(): void
    {
        // 32 random bytes, base64url-encoded without padding.
        self::assertSame(43, strlen($this->sut->generate()));
    }

    public function testTokensAreUnique(): void
    {
        $tokens = [];
        for ($i = 0; $i < 100; $i++) {
            $tokens[$this->sut->generate()] = true;
        }

        self::assertCount(100, $tokens, 'expected 100 distinct tokens');
    }

    public function testCustomEntropyLength(): void
    {
        // 48 bytes → 64 base64 chars, no padding (48 is divisible by 3).
        self::assertSame(64, strlen($this->sut->generate(48)));
    }

    public function testRejectsWeakEntropy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->generate(8);
    }
}
