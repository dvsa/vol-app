<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Auth\Exception\DeleteUserException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

final class DeleteUserSelfserve extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface,
    CacheAwareInterface
{
    use AuthAwareTrait;
    use CacheAwareTrait;

    public const string ADMIN_ROLE_ERROR = 'error-always-one-operator-admin';

    protected $repoServiceName = 'User';

    protected $extraRepos = ['OrganisationUser'];

    public function __construct(private ValidatableAdapterInterface $adapter)
    {
    }

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\User\DeleteUserSelfserve $command command
     * @throws BadRequestException
     * @throws DeleteUserException
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        if ((int)$command->getId() === $this->getCurrentUser()->getId()) {
            throw new BadRequestException('You can not delete yourself');
        }

        /** @var \Dvsa\Olcs\Api\Entity\User\User $user */
        $user = $this->getRepo()->fetchUsingId($command);

        //only internal users can delete the last operator admin
        if ($user->isLastOperatorAdmin() && !$this->isGranted(Permission::CAN_MANAGE_USER_INTERNAL)) {
            throw new BadRequestException(self::ADMIN_ROLE_ERROR);
        }

        $userId = $user->getId();

        $this->getRepo('OrganisationUser')->deleteByUserId($userId);
        $this->getRepo()->delete($user);

        $cognitoDeleteResult = $this->adapter->deleteUser($user->getLoginId());
        assert($cognitoDeleteResult instanceof DeleteUserResult);

        if (!$cognitoDeleteResult->isValid() && !$cognitoDeleteResult->isUserNotPresent()) {
            throw new DeleteUserException('Could not delete user');
        }

        $organisation = $user->getRelatedOrganisation();

        if ($organisation instanceof Organisation) {
            $this->clearOrganisationCaches($organisation);
        } else {
            $this->clearUserCaches([$userId]);
        }

        $result = new Result();
        $result->addId('user', $userId);
        $result->addMessage('User deleted successfully');

        return $result;
    }
}
