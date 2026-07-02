<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\Command\Auth\ResetPassword;

/**
 * @see ResetPassword
 */
class ResetPasswordTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $password = 'password';
        $username = 'username';
        $realm = 'realm';
        $confirmationId = 'confirmation id';
        $tokenId = 'token id';

        $data = [
            'password' => $password,
            'username' => $username,
            'realm' => $realm,
            'confirmationId' => $confirmationId,
            'tokenId' => $tokenId,
        ];

        $command = ResetPassword::create($data);

        $this->assertEquals($password, $command->getPassword());
        $this->assertEquals($username, $command->getUsername());
        $this->assertEquals($realm, $command->getRealm());
        $this->assertEquals($confirmationId, $command->getConfirmationId());
        $this->assertEquals($tokenId, $command->getTokenId());
    }
}
