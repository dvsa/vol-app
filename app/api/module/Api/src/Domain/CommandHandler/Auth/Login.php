<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Exception\UserHasNoOrganisationException;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Exception\UserIdentityAmbiguousException;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Exception\UserIsNotEnabledException;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Exception\UserNotFoundException;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Exception\UserRealmMismatchException;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Exception\UserSoftDeletedException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Domain\Util\DoctrineExtension\Logger;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Service\AuthenticationServiceInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Authentication\AuthenticationService;

class Login extends AbstractCommandHandler
{
    public const FAILURE_ACCOUNT_DISABLED = -5;

    public const REALM_INTERNAL = 'internal';
    public const REALM_SELFSERVE = 'selfserve';

    public const SELFSERVE_ROLE_ORG_CHECK = [
        Role::ROLE_OPERATOR_ADMIN,
        Role::ROLE_OPERATOR_USER,
        Role::ROLE_OPERATOR_TM
    ];

    /** @var AuthenticationServiceInterface */
    protected $authenticationService;

    /**
     * @var ValidatableAdapterInterface
     */
    protected $adapter;

    protected $repoServiceName = 'User';

    /**
     * Login constructor.
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationServiceInterface $authenticationService, ValidatableAdapterInterface $adapter)
    {
        $this->authenticationService = $authenticationService;
        $this->adapter = $adapter;
    }

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof \Dvsa\Olcs\Transfer\Command\Auth\Login);

        $this->adapter->setIdentity($command->getUsername());
        $this->adapter->setCredential($command->getPassword());

        try {
            $user = $this->findUserInDatabaseByLoginIdOrFail($command->getUsername());
            $this->checkUserIsNotSoftDeletedOrFail($user);
            $this->checkUserAccountEnabledOrFail($user);
            $this->checkUserCanAccessRealmOrFail($user, $command->getRealm());
            $this->checkUserIfServeServeHasOrganisationOrFail($user);
            $result = $this->authenticationService->authenticate($this->adapter);
            if ($result->isValid()) {
                $this->updateUserLastLoginAt($user);
            }
        } catch (UserNotFoundException | UserSoftDeletedException $e) {
            $result = new \Laminas\Authentication\Result(
                \Laminas\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND,
                [],
                [$e->getMessage()]
            );
        } catch (UserIdentityAmbiguousException $e) {
            $result = new \Laminas\Authentication\Result(
                \Laminas\Authentication\Result::FAILURE_IDENTITY_AMBIGUOUS,
                [],
                [$e->getMessage()]
            );
        } catch (UserIsNotEnabledException $e) {
            $result = new \Laminas\Authentication\Result(
                self::FAILURE_ACCOUNT_DISABLED,
                [],
                [$e->getMessage()]
            );
        } catch (UserRealmMismatchException | UserHasNoOrganisationException $e) {
            $result = new \Laminas\Authentication\Result(
                \Laminas\Authentication\Result::FAILURE_CREDENTIAL_INVALID,
                [],
                [$e->getMessage()]
            );
        }

        if ($result->isValid()) {
            \Olcs\Logging\Log\Logger::debug(sprintf(
                'Successful authentication attempt from "%s" with code "%s"',
                $command->getUsername(),
                $result->getCode()
            ));
        } else {
            \Olcs\Logging\Log\Logger::info(sprintf(
                'Unsuccessful authentication attempt from "%s" with message "%s"',
                $command->getUsername(),
                implode(' -- ', $result->getMessages())
            ));
        }

        $this->result->setFlag('isValid', $result->isValid());
        $this->result->setFlag('code', $result->getCode());
        $this->result->setFlag('identity', $result->getIdentity());
        $this->result->setFlag('messages', $result->getMessages());

        return $this->result;
    }

    protected function updateUserLastLoginAt(User $user): User
    {
        $this->getRepo()->updateLastLogin($user, new DateTime(), $user);

        return $user;
    }

    /**
     * @throws RuntimeException
     * @throws UserNotFoundException
     * @throws UserIdentityAmbiguousException
     */
    protected function findUserInDatabaseByLoginIdOrFail(string $username): User
    {
        $repo = $this->getRepo();
        assert($repo instanceof UserRepository);
        $user = $repo->fetchByLoginId($username);
        switch (count($user)) {
            case 0:
                throw new UserNotFoundException(sprintf(
                    'User with login_id "%s" does not exist in the database.',
                    $username
                ));
            case 1:
                $user = $user[0];
                assert($user instanceof User);
                return $user;
            default:
                throw new UserIdentityAmbiguousException(sprintf(
                    'User with login_id "%s" returns more than 1 user; user identity ambiguous',
                    $username
                ));
        }
    }

    /**
     * Checks that the user is enabled.
     *
     * @return bool
     * @throws UserIsNotEnabledException
     */
    protected function checkUserAccountEnabledOrFail(User $user): bool
    {
        if ($user->isDisabled()) {
            throw new UserIsNotEnabledException(sprintf(
                'User with login_id "%s" is disabled',
                $user->getLoginId()
            ));
        }
        return true;
    }

    /**
     * Checks that the user is not soft-deleted.
     *
     * @return bool
     * @throws UserSoftDeletedException
     */
    protected function checkUserIsNotSoftDeletedOrFail(User $user): bool
    {
        if (!empty($user->getDeletedDate())) {
            throw new UserSoftDeletedException(sprintf(
                'User with login_id "%s" has been soft-deleted',
                $user->getLoginId()
            ));
        }
        return true;
    }

    /**
     * Performs a user realm check.
     *
     * A user can ONLY log into a realm (SelfServe or Internal) they are assigned as.
     *
     * @return bool
     * @throws UserRealmMismatchException
     */
    protected function checkUserCanAccessRealmOrFail(User $user, string $realm): bool
    {
        $userRealm = $user->isInternal() ? static::REALM_INTERNAL : static::REALM_SELFSERVE;
        if ($userRealm !== $realm) {
            throw new UserRealmMismatchException(sprintf(
                'User with login_id "%s" with realm "%s" is attempting to log in to realm "%s"',
                $user->getLoginId(),
                $userRealm,
                $realm
            ));
        }
        return true;
    }

    /**
     * Performs a user org check.
     *
     * A user can ONLY log into a self-serve realm if they have a related organisation.
     *
     * @return bool
     * @throws UserHasNoOrganisationException
     */
    protected function checkUserIfServeServeHasOrganisationOrFail(User $user): bool
    {
        if ($user->isInternal() || !$user->hasRoles(self::SELFSERVE_ROLE_ORG_CHECK)) {
            return true;
        }

        if ($user->getRelatedOrganisation() === null) {
            throw new UserHasNoOrganisationException(sprintf(
                'User with login_id "%s" with selfserve realm has no organisation attached',
                $user->getLoginId()
            ));
        }
        return true;
    }
}
