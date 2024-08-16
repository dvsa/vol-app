<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Olcs\Api\Domain\Command\Email\SendForgotPassword as ForgotPasswordEmailCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\UserPasswordReset as UserPasswordResetRepo;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\Auth\ForgotPassword as ForgotPasswordCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

class ForgotPassword extends AbstractCommandHandler implements ConfigAwareInterface
{
    use ConfigAwareTrait;
    use QueueAwareTrait;

    protected $repoServiceName = 'UserPasswordReset';
    protected $extraRepos = ['User'];

    public const MSG_USER_NOT_FOUND = 'auth.forgot-password.user-not-found';
    public const MSG_USER_NOT_ALLOWED_RESET = 'auth.forgot-password.not-eligible';

    public function __construct(private ValidatableAdapterInterface $authAdapter, private PasswordService $passwordService)
    {
    }

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws ClientException|RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof ForgotPasswordCmd);

        $userRepo = $this->getRepo('User');
        assert($userRepo instanceof UserRepo);

        $user = $userRepo->fetchEnabledIdentityByLoginId($command->getUsername());

        if ($user === null) {
            $this->result->setFlag('success', false);
            $this->result->addMessage(self::MSG_USER_NOT_FOUND);
            return $this->result;
        }

        assert($user instanceof UserEntity);

        if (!$user->canResetPassword()) {
            $this->result->setFlag('success', false);
            $this->result->addMessage(self::MSG_USER_NOT_ALLOWED_RESET);
            return $this->result;
        }

        // Register user in Cognito if they haven't been migrated via login yet
        $this->authAdapter->registerIfNotPresent(
            $user->getLoginId(),
            $this->passwordService->generatePassword(),
            $user->getContactDetails()->getEmailAddress()
        );

        $confirmation = hash('sha256', random_bytes(512));
        $entity = UserPasswordResetEntity::create($user, $confirmation);

        $userPasswordResetRepo = $this->getRepo('UserPasswordReset');
        assert($userPasswordResetRepo instanceof UserPasswordResetRepo);
        $userPasswordResetRepo->save($entity);

        $id = $entity->getId();

        $emailCmdData = [
            'id' => $id,
            'realm' => $command->getRealm(),
        ];

        $emailQueueCmd = $this->emailQueue(ForgotPasswordEmailCmd::class, $emailCmdData, $id);
        $this->result->merge(
            $this->handleSideEffect($emailQueueCmd)
        );

        $this->result->addId('UserPasswordReset', $id);
        $this->result->setFlag('success', true);

        return $this->result;
    }
}
