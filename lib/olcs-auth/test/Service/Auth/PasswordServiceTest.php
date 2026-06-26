<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response as CqrsResponse;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;
use Dvsa\Olcs\Transfer\Command\Auth\ForgotPassword;
use Dvsa\Olcs\Transfer\Command\Auth\ResetPassword;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response as LaminasResponse;

/**
 * @see PasswordService
 */
class PasswordServiceTest extends MockeryTestCase
{
    public function testResetPassword(): void
    {
        $realm = 'the-realm';
        $password = 'password';
        $username = 'username';
        $tokenId = 'tokenId';
        $confirmationId = 'confirmationId';

        $data = [
            'realm' => $realm,
            'password' => $password,
            'username' => $username,
            'tokenId' => $tokenId,
            'confirmationId' => $confirmationId,
        ];

        $laminasResponse = m::mock(LaminasResponse::class);
        $cqrsResponse = $this->cqrsResponse($laminasResponse);

        $commandSender = m::mock(CommandSender::class);
        $commandSender->shouldReceive('send')->with(m::type(ResetPassword::class))->andReturnUsing(
            function (ResetPassword $resetPasswordCmd) use ($data, $cqrsResponse) {
                $this->assertEquals($resetPasswordCmd->getArrayCopy(), $data);
                return $cqrsResponse;
            }
        );

        $sut = new PasswordService($commandSender, $this->responseDecoder($laminasResponse), $realm);
        $result = $sut->resetPassword($username, $confirmationId, $tokenId, $password);
        $this->assertEquals($this->expectedResponse(), $result);
    }

    public function testUpdatePassword(): void
    {
        $realm = 'the-realm';
        $oldPassword = 'old-password';
        $newPassword = 'new-password';

        $data = [
            'password' => $oldPassword,
            'newPassword' => $newPassword,
        ];

        $laminasResponse = m::mock(LaminasResponse::class);
        $cqrsResponse = $this->cqrsResponse($laminasResponse);

        $commandSender = m::mock(CommandSender::class);
        $commandSender->shouldReceive('send')->with(m::type(ChangePassword::class))->andReturnUsing(
            function (ChangePassword $changePasswordCmd) use ($data, $cqrsResponse) {
                $this->assertEquals($changePasswordCmd->getArrayCopy(), $data);
                return $cqrsResponse;
            }
        );

        $sut = new PasswordService($commandSender, $this->responseDecoder($laminasResponse), $realm);
        $result = $sut->updatePassword($oldPassword, $newPassword);
        $this->assertEquals($this->expectedResponse(), $result);
    }

    public function testForgotPassword(): void
    {
        $realm = 'the-realm';
        $username = 'username';

        $data = [
            'realm' => $realm,
            'username' => $username,
        ];

        $laminasResponse = m::mock(LaminasResponse::class);
        $cqrsResponse = $this->cqrsResponse($laminasResponse);

        $commandSender = m::mock(CommandSender::class);
        $commandSender->shouldReceive('send')->with(m::type(ForgotPassword::class))->andReturnUsing(
            function (ForgotPassword $changePasswordCmd) use ($data, $cqrsResponse) {
                $this->assertEquals($changePasswordCmd->getArrayCopy(), $data);
                return $cqrsResponse;
            }
        );

        $sut = new PasswordService($commandSender, $this->responseDecoder($laminasResponse), $realm);
        $result = $sut->forgotPassword($username);
        $this->assertEquals($this->expectedResponse(), $result);
    }

    private function cqrsResponse($laminasResponse): m\MockInterface
    {
        $cqrsResponse = m::mock(CqrsResponse::class);
        $cqrsResponse->shouldReceive('getHttpResponse')->withNoArgs()->andReturn($laminasResponse);

        return $cqrsResponse;
    }

    private function responseDecoder(m\MockInterface $laminasResponse): m\MockInterface|ResponseDecoderService
    {
        $responseDecoder = m::mock(ResponseDecoderService::class);
        $responseDecoder->shouldReceive('decode')->with($laminasResponse)->andReturn($this->expectedResponse());

        return $responseDecoder;
    }

    private function expectedResponse(): array
    {
        return [
            'status' => 200,
            'flags' => [
                'success' => true,
            ],
        ];
    }
}
