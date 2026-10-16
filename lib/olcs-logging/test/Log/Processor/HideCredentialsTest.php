<?php

declare(strict_types=1);

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\HideCredentials;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HideCredentialsTest extends TestCase
{
    private const HIDDEN = HideCredentials::REPLACE_WITH;

    private const JWT = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dBjftJeZ4CVPmB92K27uhbUJU1p1r_wW1gFWFOEjXk';

    #[DataProvider('sensitiveKeyProvider')]
    public function testRedactsSensitiveKeysWhateverTheirSpelling(string $key): void
    {
        $result = $this->process([$key => 'sensitive-value']);

        $this->assertSame([$key => self::HIDDEN], $result->context);
    }

    public static function sensitiveKeyProvider(): array
    {
        return [
            ['Authorization'],
            ['authorization'],
            ['Cookie'],
            ['Set-Cookie'],
            ['refreshToken'],
            ['refresh_token'],
            ['Refresh-Token'],
            ['accessToken'],
            ['idToken'],
            ['challengeSession'],
            ['confirmationId'],
            ['tokenId'],
            ['sessionId'],
            ['apiKey'],
        ];
    }

    public function testRedactsNestedHeaders(): void
    {
        $result = $this->process([
            'headers' => [
                'Authorization' => 'Bearer ' . self::JWT,
                'Cookie' => 'Identity=abc123; other=keep',
                'Accept' => 'application/json',
            ],
            'method' => 'POST',
        ]);

        $this->assertSame(
            [
                'headers' => [
                    'Authorization' => self::HIDDEN,
                    'Cookie' => self::HIDDEN,
                    'Accept' => 'application/json',
                ],
                'method' => 'POST',
            ],
            $result->context
        );
    }

    public function testRedactsJwtUnderAnUnlistedKey(): void
    {
        // The point of the value pass: a raw body logged under "content" is not a key
        // anyone would list, but it still carries the token.
        $result = $this->process(['content' => '{"token":"' . self::JWT . '","user":"bob"}']);

        $this->assertSame(
            ['content' => '{"token":"' . self::HIDDEN . '","user":"bob"}'],
            $result->context
        );
    }

    public function testLeavesOrdinaryValuesAlone(): void
    {
        $context = [
            'licence' => 'OB1234567',
            'count' => 42,
            'note' => 'eyes on the prize',
            'nested' => ['status' => 'granted'],
        ];

        $this->assertSame($context, $this->process($context)->context);
    }

    public function testRedactsExtraAsWellAsContext(): void
    {
        $record = new LogRecord(
            new DateTimeImmutable(),
            'test',
            Level::Info,
            '',
            [],
            ['Authorization' => 'Bearer x', 'safe' => 'ok']
        );

        $result = (new HideCredentials())($record);

        $this->assertSame(['Authorization' => self::HIDDEN, 'safe' => 'ok'], $result->extra);
    }

    private function process(array $context): LogRecord
    {
        $sut = new HideCredentials();

        return $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, '', $context));
    }
}
