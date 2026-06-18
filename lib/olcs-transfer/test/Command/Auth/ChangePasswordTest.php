<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;

/**
 * @see ChangePassword
 */
class ChangePasswordTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $password = 'password';
        $newPassword = 'new password';

        $data = [
            'password' => $password,
            'newPassword' => $newPassword,
        ];

        $command = ChangePassword::create($data);

        $this->assertEquals($password, $command->getPassword());
        $this->assertEquals($newPassword, $command->getNewPassword());
    }
}
