<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;
use Dvsa\Olcs\Transfer\Command\Auth\ForgotPassword;
use Dvsa\Olcs\Transfer\Command\Auth\ResetPassword;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class PasswordService
{
    public function __construct(private CommandSender $commandSender, private ResponseDecoderService $responseDecoder, private string $realm)
    {
    }

    /**
     * Update password
     *
     * @param string  $oldPassword Old password
     * @param string  $newPassword New password
     */
    public function updatePassword($oldPassword, $newPassword): array
    {
        $data = [
            'realm' => $this->realm,
            'password' => $oldPassword,
            'newPassword' => $newPassword,
        ];

        $command = ChangePassword::create($data);
        return $this->response($command);
    }

    /**
     * Reset password
     *
     * @param string $username       Username
     * @param string $confirmationId Confirmation id
     * @param string $tokenId        Token id
     * @param string $newPassword    New password
     */
    public function resetPassword($username, $confirmationId, $tokenId, $newPassword): array
    {
        $data = [
            'realm' => $this->realm,
            'password' => $newPassword,
            'username' => $username,
            'tokenId' => $tokenId,
            'confirmationId' => $confirmationId
        ];

        $command = ResetPassword::create($data);
        return $this->response($command);
    }

    /**
     * Forgot password
     *
     * @param string $username Username
     */
    public function forgotPassword($username): array
    {
        $data = [
            'realm' => $this->realm,
            'username' => $username,
        ];

        $command = ForgotPassword::create($data);
        return $this->response($command);
    }


    private function response(CommandInterface $command): array
    {
        return $this->responseDecoder->decode(
            $this->commandSender->send($command)->getHttpResponse()
        );
    }
}
