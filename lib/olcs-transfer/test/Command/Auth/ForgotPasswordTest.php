<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\Command\Auth\ForgotPassword;

/**
 * @see ForgotPassword
 */
class ForgotPasswordTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $username = 'username';
        $realm = 'realm';

        $data = [
            'username' => $username,
            'realm' => $realm,
        ];

        $command = ForgotPassword::create($data);

        $this->assertEquals($username, $command->getUsername());
        $this->assertEquals($realm, $command->getRealm());
    }
}
