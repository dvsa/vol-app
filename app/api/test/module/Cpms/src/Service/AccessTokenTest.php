<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('isExpiredDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isExpired(int $issuedAt, int $expiresIn, bool $isExpired): void
    {
        $accessToken = new AccessToken(
            "accessToken",
            $expiresIn,
            $issuedAt,
            "scope",
            "Bearer"
        );

        $this->assertEquals($isExpired, $accessToken->isExpired());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getAuthorisationHeader(): void
    {
        $accessToken = new AccessToken(
            "accessToken",
            12345,
            12344,
            "scope",
            "Bearer"
        );

        $this->assertStringStartsWith('Bearer ', $accessToken->getAuthorisationHeader());
    }


    public static function isExpiredDataProvider(): array
    {
        return [
            'has expired' => [
                'issuedAt' => time() - 300,
                'expiresIn' => 240,
                'isExpired' => true
            ],
            "hasn't expired" => [
                'issuedAt' => time(),
                'expiresIn' => 3600,
                'isExpired' => false
            ]
        ];
    }
}
