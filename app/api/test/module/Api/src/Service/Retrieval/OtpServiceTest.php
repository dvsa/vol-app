<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Retrieval;

use Dvsa\Olcs\Api\Service\Retrieval\OtpService;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

final class OtpServiceTest extends TestCase
{
    private OtpService $sut;

    protected function setUp(): void
    {
        $this->sut = new OtpService();
    }

    public function testGeneratedCodeIsSixDigits(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $code = $this->sut->generateCode();
            self::assertMatchesRegularExpression('/^[0-9]{6}$/', $code, 'code must be exactly 6 numeric digits, zero-padded');
        }
    }

    public function testCorrectCodeVerifies(): void
    {
        $code = $this->sut->generateCode();
        $hash = $this->sut->hash($code);

        self::assertTrue($this->sut->verify($code, $hash));
    }

    public function testWrongCodeDoesNotVerify(): void
    {
        $hash = $this->sut->hash('123456');

        self::assertFalse($this->sut->verify('654321', $hash));
    }

    public function testPlaintextIsNotRecoverableFromHash(): void
    {
        $code = '424242';

        self::assertStringNotContainsString($code, $this->sut->hash($code));
    }

    public function testVerifyRejectsEmptyHash(): void
    {
        self::assertFalse($this->sut->verify('123456', ''));
    }

    public function testExpiryIsTenMinutesAhead(): void
    {
        $now = new \DateTimeImmutable('2026-07-20 09:00:00');

        self::assertSame('2026-07-20 09:10:00', $this->sut->expiryFrom($now)->format('Y-m-d H:i:s'));
    }
}
