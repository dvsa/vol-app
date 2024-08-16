<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\UserPasswordReset as UserPasswordResetRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Transfer\Command\Auth\ResetPassword as ResetPasswordCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Olcs\Logging\Log\Logger;

class ResetPassword extends AbstractCommandHandler
{
    protected $repoServiceName = 'UserPasswordReset';

    public const MSG_EXPIRED_LINK = 'auth.forgot-password-expired';
    public const MSG_GENERIC_FAIL = 'auth.reset-password.fail';
    public const MSG_GENERIC_SUCCESS = 'auth.reset-password.success';
    public const MSG_FAIL_MISSING_CONFIRMATION = '%s failed to reset password, confirmation id not found: %s';
    public const MSG_FAIL_NOT_VALID = '%s failed to reset password, reset was not valid';
    public const MSG_FAIL_DEBUG_LOG = '%s failed to reset password using cognito';
    public const MSG_FAIL_COGNITO_EXCEPTION = '%s failed to reset password due to cognito exception: %s';

    public function __construct(private readonly ValidatableAdapterInterface $adapter, private readonly EventHistoryCreator $eventHistoryCreator)
    {
    }

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof ResetPasswordCmd);

        $userPasswordResetRepo = $this->getRepo('UserPasswordReset');
        assert($userPasswordResetRepo instanceof UserPasswordResetRepo);

        $username = $command->getUsername();
        $confirmationId = $command->getConfirmationId();

        try {
            $userPasswordReset = $userPasswordResetRepo->fetchOneByConfirmation([$confirmationId]);
            assert($userPasswordReset instanceof UserPasswordResetEntity);
        } catch (NotFoundException $e) {
            $logMessage = sprintf(self::MSG_FAIL_MISSING_CONFIRMATION, $username, $confirmationId);
            Logger::debug($logMessage);
            $this->result->setFlag('success', false);
            $this->result->addMessage(self::MSG_EXPIRED_LINK);
            return $this->result;
        }

        if (!$userPasswordReset->isValid($username)) {
            $logMessage = sprintf(self::MSG_FAIL_NOT_VALID, $username);
            Logger::debug($logMessage);
            $this->result->setFlag('success', false);
            $this->result->addMessage(self::MSG_EXPIRED_LINK);
            return $this->result;
        }

        try {
            $success = $this->adapter->resetPassword(
                $username,
                $command->getPassword()
            );

            if ($success) {
                $userPasswordReset->setSuccess(true);
                $userPasswordResetRepo->save($userPasswordReset);

                $this->result->setFlag('success', true);
                $this->result->addMessage(self::MSG_GENERIC_SUCCESS);
                $this->result->addId($this->repoServiceName, $userPasswordReset->getId());

                $this->eventHistoryCreator->create(
                    $userPasswordReset->getUser(),
                    EventHistoryTypeEntity::EVENT_CODE_PASSWORD_RESET
                );

                return $this->result;
            }

            $this->result->setFlag('success', false);
            $this->result->addMessage(self::MSG_GENERIC_FAIL);
            $logMessage = sprintf(self::MSG_FAIL_DEBUG_LOG, $username);
            Logger::debug($logMessage);
        } catch (\Exception $e) {
            $this->result->setFlag('success', false);
            $this->result->addMessage(self::MSG_GENERIC_FAIL);
            $logMessage = sprintf(self::MSG_FAIL_COGNITO_EXCEPTION, $username, $e->getMessage());
            Logger::err($logMessage);
        }

        return $this->result;
    }
}
