<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Transfer\Command\Auth\ChangeExpiredPassword as ChangeExpiredPasswordCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

/**
 * @see ChangeExpiredPasswordFactory
 */
class ChangeExpiredPassword extends AbstractCommandHandler
{
    public const ERROR_USER_NOT_FOUND = 'Updating lastLoginAt failed: loginId is not found in User table';

    //seems sensible to use the same keys as the regular change password
    public const MSG_GENERIC_FAIL = 'auth.change-password.fail';
    public const MSG_GENERIC_SUCCESS = 'auth.change-password.success';
    public const MSG_NOT_AUTHORIZED = 'auth.change-password.not-authorized';
    public const MSG_INVALID = 'auth.change-password.invalid';

    public function __construct(protected ValidatableAdapterInterface $adapter, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof ChangeExpiredPasswordCommand);

        $changeResult = $this->adapter->changeExpiredPassword(
            $command->getNewPassword(),
            $command->getChallengeSession(),
            $command->getUsername(),
        );

        $this->updateUserLastLoginAt($changeResult, $command->getUsername());

        $isValid = $changeResult->isValid();
        $this->result->setFlag('isValid', $isValid);
        $this->result->setFlag('code', $changeResult->getCode());
        $this->result->setFlag('identity', $changeResult->getIdentity());
        $this->result->setFlag('options', $changeResult->getOptions());

        $message = self::MSG_GENERIC_SUCCESS;

        if (!$isValid) {
            $code = $changeResult->getCode();

            $message = match ($code) {
                ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID => self::MSG_INVALID,
                ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED => self::MSG_NOT_AUTHORIZED,
                default => self::MSG_GENERIC_FAIL,
            };
        }

        $this->result->setFlag('messages', [$message]);
        return $this->result;
    }

    /**
     * @throws RuntimeException
     */
    protected function updateUserLastLoginAt(ChangeExpiredPasswordResult $result, string $loginId): void
    {
        if ($result->getCode() !== ChangeExpiredPasswordResult::SUCCESS) {
            return;
        }

        $user = $this->userRepository->fetchByLoginId($loginId)[0] ?? null;
        if (is_null($user)) {
            throw new RuntimeException(static::ERROR_USER_NOT_FOUND);
        }
        assert($user instanceof User);
        $user->setLastLoginAt(new \DateTime());
        $this->userRepository->save($user);
    }
}
