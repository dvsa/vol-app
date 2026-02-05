<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

final class UpdateUserSelfserve extends AbstractUserCommandHandler implements
    TransactionedInterface,
    CacheAwareInterface,
    ConfigAwareInterface
{
    use CacheAwareTrait;
    use ConfigAwareTrait;

    public const string ADMIN_ROLE_ERROR = 'error-always-one-operator-admin';

    protected $repoServiceName = 'User';

    protected $extraRepos = ['ContactDetails'];

    /**
     * @var mixed
     */
    private string $provider;

    public function __construct(private ValidatableAdapterInterface $authAdapter, private PasswordService $passwordService, private EventHistoryCreator $eventHistoryCreator)
    {
    }

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Contracts\Auth\Exceptions\ClientException
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->provider = $this->getConfig()['auth']['identity_provider'];

        /** @var User $user */
        $user = $this->getRepo()->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());

        $data = $command->getArrayCopy();

        if ($data['permission'] !== User::PERMISSION_ADMIN && $user->isLastOperatorAdmin()) {
            throw new BadRequestException(self::ADMIN_ROLE_ERROR);
        }

        // populate roles based on the user type and permission
        $data['roles'] = User::getRolesByUserType($user->getUserType(), $data['permission']);

        $this->checkEmailUpdate($user, $command);

        $user->update(
            $this->getRepo()->populateRefDataReference($data)
        );

        // Forename and surname cannot be updated from self-serve
        $canUpdatePerson = false;
        if ($user->getContactDetails() instanceof ContactDetails) {
            // update existing contact details
            $user->getContactDetails()->update(
                $this->getRepo('ContactDetails')->populateRefDataReference(
                    $command->getContactDetails()
                ),
                $canUpdatePerson
            );
        } else {
            // create new contact details
            $user->setContactDetails(
                ContactDetails::create(
                    $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_USER),
                    $this->getRepo('ContactDetails')->populateRefDataReference(
                        $command->getContactDetails()
                    )
                )
            );
        }

        $this->getRepo()->save($user);

        if ($this->authAdapter->doesUserExist($user->getLoginId())) {
            $this->authAdapter->changeAttribute($user->getLoginId(), 'email', $user->getContactDetails()->getEmailAddress());
        }

        $userId = $user->getId();

        $result = new Result();
        $result->addId('user', $userId);
        $result->addMessage('User updated successfully');

        $organisation = $user->getRelatedOrganisation();
        $userId = $user->getId();

        if ($organisation instanceof Organisation) {
            $this->clearOrganisationCaches($organisation);
        } else {
            $this->clearUserCaches([$userId]);
        }

        return $result;
    }

    private function checkEmailUpdate(User $user, CommandInterface $command)
    {
        if ($user->getContactDetails()->getEmailAddress() != $command->getContactDetails()['emailAddress']) {
            $this->eventHistoryCreator->create($user, EventHistoryTypeEntity::USER_EMAIL_ADDRESS_UPDATED, 'Old:' . $user->getContactDetails()->getEmailAddress() . ' New:' . $command->getContactDetails()['emailAddress']);
        }
    }
}
