<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\Command\Email\SendForgotPassword;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ForgotPassword;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\UserPasswordReset as UserPasswordResetRepo;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\Auth\ForgotPassword as ForgotPasswordCmd;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Mockery as m;

/**
 * @see ForgotPassword
 */
class ForgotPasswordTest extends AbstractCommandHandlerTestCase
{
    private string $username = 'username';
    private string $realm = 'realm';
    private ForgotPasswordCmd $command;

    public function setUp(): void
    {
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('UserPasswordReset', UserPasswordResetRepo::class);

        $this->mockedSmServices = [
            'config' => $this->getConfig('not openam'),
        ];

        $this->command = $this->getCommand();
        $this->sut = new ForgotPassword(m::mock(ValidatableAdapterInterface::class)->shouldIgnoreMissing(), m::mock(PasswordService::class)->shouldIgnoreMissing());

        parent::setUp();
    }

    public function testHandleCommandUserNotFound(): void
    {
        $this->repoMap['User']->expects('fetchEnabledIdentityByLoginId')->with($this->username)->andReturnNull();

        $result = $this->sut->handleCommand($this->command);
        $this->assertEquals(ForgotPassword::MSG_USER_NOT_FOUND, $result->getMessages()[0]);
        $this->assertFalse($result->getFlag('success'));
    }

    public function testHandleCommandUserNotAllowedToReset(): void
    {
        $user = m::mock(UserEntity::class);
        $user->expects('canResetPassword')->withNoArgs()->andReturnFalse();

        $this->repoMap['User']->expects('fetchEnabledIdentityByLoginId')->with($this->username)->andReturn($user);

        $result = $this->sut->handleCommand($this->command);
        $this->assertEquals(ForgotPassword::MSG_USER_NOT_ALLOWED_RESET, $result->getMessages()[0]);
        $this->assertFalse($result->getFlag('success'));
    }

    public function testHandleCommandSuccessWithUserCreate(): void
    {
        $id = 111;

        $contactDetails = m::mock(ContactDetailsEntity::class);
        $contactDetails->expects('getEmailAddress')->withNoArgs()->andReturn('test@example.com');

        $user = m::mock(UserEntity::class);
        $user->expects('canResetPassword')->withNoArgs()->andReturnTrue();
        $user->expects('getLoginId')->withNoArgs()->andReturn($id);
        $user->expects('getContactDetails')->withNoArgs()->andReturn($contactDetails);

        $this->repoMap['User']->expects('fetchEnabledIdentityByLoginId')->with($this->username)->andReturn($user);

        $this->repoMap['UserPasswordReset']->expects('save')
            ->with(m::type(UserPasswordResetEntity::class))
            ->andReturnUsing(
                function (UserPasswordResetEntity $entity) use (&$savedEntity, $id) {
                    $entity->setId($id);
                    $savedEntity = $entity;

                    return $savedEntity;
                }
            );

        $queueId = 1234;

        $emailCmdData = [
            'id' => $id,
            'realm' => $this->realm,
        ];

        $queueResult = new Result();
        $queueResult->addId('Queue', $queueId);

        $this->expectedEmailQueueSideEffect(
            SendForgotPassword::class,
            $emailCmdData,
            $id,
            $queueResult
        );

        $expected = [
            'id' => [
                'UserPasswordReset' => $id,
                'Queue' => $queueId,
            ],
            'flags' => [
                'success' => true,
            ],
            'messages' => [],
        ];

        $result = $this->sut->handleCommand($this->command);
        $this->assertEquals($expected, $result->toArray());
    }

    private function getConfig(string $adapter): array
    {
        return [
            'auth' => [
                'default_adapter' => $adapter
            ],
        ];
    }

    private function getCommand(): ForgotPasswordCmd
    {
        $cmdData = $this->getCmdData();
        return ForgotPasswordCmd::create($cmdData);
    }

    private function getCmdData(): array
    {
        return [
            'username' => $this->username,
            'realm' => $this->realm,
        ];
    }
}
