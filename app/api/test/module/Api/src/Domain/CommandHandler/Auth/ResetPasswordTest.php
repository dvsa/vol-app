<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ResetPassword;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\UserPasswordReset as UserPasswordResetRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Transfer\Command\Auth\ResetPassword as ResetPasswordCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Mockery as m;

/**
 * @see ResetPassword
 */
class ResetPasswordTest extends AbstractCommandHandlerTestCase
{
    private m\MockInterface $adapter;
    private m\MockInterface $eventHistoryCreator;
    private string $username = 'username';
    private string $password = 'password';
    private string $realm = 'realm';
    private string $confirmationId = 'confirmation';
    private string $tokenId = 'token';
    private ResetPasswordCmd $command;

    public function setUp(): void
    {
        $this->adapter = m::mock(ValidatableAdapterInterface::class);
        $this->eventHistoryCreator = m::mock(EventHistoryCreator::class);

        $this->mockRepo('UserPasswordReset', UserPasswordResetRepo::class);

        $this->mockedSmServices = [
            ValidatableAdapterInterface::class => $this->adapter,
            'EventHistoryCreator' => $this->eventHistoryCreator,
        ];

        $this->command = $this->getCommand();
        $this->sut = new ResetPassword($this->adapter, $this->eventHistoryCreator);

        parent::setUp();
    }

    public function testHandleCommandResetNotFound(): void
    {
        $this->repoMap['UserPasswordReset']
            ->expects('fetchOneByConfirmation')
            ->with([$this->confirmationId])
            ->andThrow(new NotFoundException());

        $expectedResult = $this->expectedFailureResult(ResetPassword::MSG_EXPIRED_LINK);
        $this->assertEquals($expectedResult, $this->sut->handleCommand($this->command)->toArray());
    }

    public function testHandleCommandResetNotValid(): void
    {
        $userPasswordReset = $this->passwordReset(false);
        $this->fetchReset($userPasswordReset);

        $expectedResult = $this->expectedFailureResult(ResetPassword::MSG_EXPIRED_LINK);
        $this->assertEquals($expectedResult, $this->sut->handleCommand($this->command)->toArray());
    }

    public function testHandleCommandAdapterResetFail(): void
    {
        $userPasswordReset = $this->passwordReset(true);
        $this->fetchReset($userPasswordReset);
        $this->adapterAttempt(false);

        $expectedResult = $this->expectedFailureResult(ResetPassword::MSG_GENERIC_FAIL);
        $this->assertEquals($expectedResult, $this->sut->handleCommand($this->command)->toArray());
    }

    public function testHandleCommandAdapterException(): void
    {
        $userPasswordReset = $this->passwordReset(true);
        $this->fetchReset($userPasswordReset);

        $this->adapter
            ->expects('resetPassword')
            ->with($this->username, $this->password)
            ->andThrow(new \Exception());

        $expectedResult = $this->expectedFailureResult(ResetPassword::MSG_GENERIC_FAIL);
        $this->assertEquals($expectedResult, $this->sut->handleCommand($this->command)->toArray());
    }

    public function testHandleCommandSuccess(): void
    {
        $id = 111;
        $userPasswordReset = $this->passwordReset(true);
        $userPasswordReset->expects('setSuccess')->with(true);
        $user = m::mock(UserEntity::class);

        $this->fetchReset($userPasswordReset);

        $this->repoMap['UserPasswordReset']->expects('save')
            ->with($userPasswordReset)
            ->andReturnUsing(
                function (UserPasswordResetEntity $userPasswordReset) use (&$savedEntity, $id, $user) {
                    $userPasswordReset->expects('getId')->withNoArgs()->andReturn($id);
                    $userPasswordReset->expects('getUser')->withNoArgs()->andReturn($user);
                    $savedEntity = $userPasswordReset;

                    return $savedEntity;
                }
            );

        $this->eventHistoryCreator->expects('create')
            ->with($user, EventHistoryTypeEntity::EVENT_CODE_PASSWORD_RESET);

        $this->adapterAttempt(true);

        $expectedResult = [
            'id' => [
                'UserPasswordReset' => $id,
            ],
            'flags' => [
                'success' => true,
            ],
            'messages' => [
                0 => ResetPassword::MSG_GENERIC_SUCCESS,
            ],
        ];

        $this->assertEquals($expectedResult, $this->sut->handleCommand($this->command)->toArray());
    }

    private function adapterAttempt(bool $success): void
    {
        $this->adapter
            ->expects('resetPassword')
            ->with($this->username, $this->password)
            ->andReturn($success);
    }

    private function passwordReset(bool $isValid): m\MockInterface
    {
        $userPasswordReset = m::mock(UserPasswordResetEntity::class);
        $userPasswordReset->expects('isValid')->with($this->username)->andReturn($isValid);

        return $userPasswordReset;
    }

    private function fetchReset(m\MockInterface $userPasswordReset): void
    {
        $this->repoMap['UserPasswordReset']
            ->expects('fetchOneByConfirmation')
            ->with([$this->confirmationId])
            ->andReturn($userPasswordReset);
    }

    private function expectedFailureResult(string $message): array
    {
        return [
            'id' => [],
            'flags' => [
                'success' => false,
            ],
            'messages' => [
                0 => $message,
            ],
        ];
    }

    private function getCommand(): ResetPasswordCmd
    {
        $cmdData = [
            'username' => $this->username,
            'password' => $this->password,
            'confirmationId' => $this->confirmationId,
            'tokenId' => $this->tokenId,
            'realm' => $this->realm,
        ];

        return ResetPasswordCmd::create($cmdData);
    }
}
