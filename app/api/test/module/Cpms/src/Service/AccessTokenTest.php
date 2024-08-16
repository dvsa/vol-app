<?php

namespace Dvsa\OlcsTest\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /**
     * @test
     * @dataProvider isExpiredDataProvider
     */
    public function isExpired(int $issuedAt, int $expiresIn, bool $isExpired)
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

    /**
     * @test
     */
    public function getAuthorisationHeader()
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


    public function isExpiredDataProvider()
    {
        return [
            'has expired' => [
                'issuedAt' => time() - 300,
                'expiresIn' => 240,
                'isExpired' => true
            ],
            "hasn't expired" => [
                'issuedAt' => time(),
                'expiresIn' => 60,
                'isExpired' => false
            ]
        ];
    }
}
