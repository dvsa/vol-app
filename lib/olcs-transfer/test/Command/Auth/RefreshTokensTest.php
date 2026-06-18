<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\Command\Auth\RefreshTokens;
use PHPUnit\Framework\TestCase;

/**
 * @see RefreshToken
 */
class RefreshTokensTest extends TestCase
{
    public function testStructure(): void
    {
        $refreshToken = 'refresh-token';
        $username = 'username';

        $data = ['refreshToken' => $refreshToken, 'username' => $username];

        $command = RefreshTokens::create($data);

        $this->assertEquals($refreshToken, $command->getRefreshToken());
    }
}
