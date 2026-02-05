<?php

/**
 * Update User
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserTemporaryPassword as SendUserTemporaryPasswordDto;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\User\UpdateUser as UpdateUserCommand;
use Exception;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

/**
 * Update User
 */
final class UpdateUser extends AbstractUserCommandHandler implements
    CacheAwareInterface,
    ConfigAwareInterface,
    TransactionedInterface
{
    use CacheAwareTrait;
    use ConfigAwareTrait;

    public const string ADMIN_ROLE_ERROR = 'error-always-one-operator-admin';
    public const string RESET_PASSWORD_BY_POST = 'post';
    public const string RESET_PASSWORD_BY_EMAIL = 'email';

    protected $extraRepos = ['Application', 'ContactDetails', 'Licence', 'EventHistory', 'EventHistoryType'];

    /**
     * @var ValidatableAdapterInterface|CognitoAdapter
     */
    protected ValidatableAdapterInterface $authAdapter;

    protected PasswordService $passwordService;
    private string $provider;

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws ForbiddenException
     * @throws ValidationException
     * @throws RuntimeException
     * @throws Exception
     */
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof UpdateUserCommand, 'Expected $command to be instance of ' . UpdateUserCommand::class);

        $this->provider = $this->getConfig()['auth']['identity_provider'];

        if (!$this->isGranted(Permission::CAN_MANAGE_USER_INTERNAL)) {
            throw new ForbiddenException('You do not have permission to manage the record');
        }

        $data = $command->getArrayCopy();

        if ((!empty($data['licenceNumber'])) && ($command->getUserType() === User::USER_TYPE_OPERATOR)) {
            // fetch licence by licence number
            $licence = $this->getRepo('Licence')->fetchByLicNo($data['licenceNumber']);
            assert($licence instanceof Licence, 'Expected $licence to be instance of ' . Licence::class);

            // link with the organisation
            $data['organisations'] = [$licence->getOrganisation()];
        } elseif ((!empty($data['application'])) && ($command->getUserType() === User::USER_TYPE_TRANSPORT_MANAGER)) {
            // fetch application by id
            $application = $this->getRepo('Application')->fetchWithLicenceAndOrg($data['application']);

            // link with the organisation
            $data['organisations'] = [$application->getLicence()->getOrganisation()];
        }
        /** @var User $user */
        $user = $this->getRepo()->fetchById($command->getId(), AbstractQuery::HYDRATE_OBJECT, $command->getVersion());
        $previousEmailAddress = $user->getContactDetails() ? $user->getContactDetails()->getEmailAddress() : null;
        $previouslyDisabled = $user->isDisabled();
        $previousLastOperatorAdmin = $user->isLastOperatorAdmin();

        // validate roles
        $this->validateRoles($data['roles'], $user->getRoles()->toArray());

        $user->update(
            $this->getRepo()->populateRefDataReference($data)
        );

        //make sure the last operator administrator hasn't lost this role
        if ($previousLastOperatorAdmin && !$user->isOperatorAdministrator()) {
            throw new ForbiddenException(self::ADMIN_ROLE_ERROR);
        }

        if ($user->getContactDetails() instanceof ContactDetails) {
            // update existing contact details
            $user->getContactDetails()->update(
                $this->getRepo('ContactDetails')->populateRefDataReference(
                    $command->getContactDetails()
                )
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

        $this->authAdapter->registerIfNotPresent($user->getLoginId(), $this->passwordService->generatePassword(), $user->getContactDetails()->getEmailAddress());

        $this->updateAuthService(
            $user,
            $previousEmailAddress,
            $previouslyDisabled
        );

        $userId = $user->getId();

        $result = new Result();
        $result->addId('user', $userId);
        $result->addMessage('User updated successfully');

        if ($command->getResetPassword() !== null) {
            $result->merge(
                $this->resetPassword($user, $command->getResetPassword())
            );
        }

        $this->clearUserCaches([$userId]);

        return $result;
    }

    /**
     * Generates a new temp password and sends letter or email
     *
     * @param User $user User
     * @param string $mode Mode
     *
     * @return Result
     * @throws Exception
     */
    private function resetPassword(User $user, string $mode): Result
    {
        $licence = null;

        if ($mode === self::RESET_PASSWORD_BY_POST) {
            $org = $user->getRelatedOrganisation();

            if (!($org instanceof Organisation)) {
                throw new ValidationException(['ERR_RESET_PASS_BY_POST_NO_ADDRESS']);
            }

            // find a licence related to an organisation the user belongs to
            $licence = $org->getRelatedLicences()->first();

            if (!($licence instanceof Licence)) {
                throw new ValidationException(['ERR_RESET_PASS_BY_POST_NO_ADDRESS']);
            }
        }

        $password = $this->passwordService->generatePassword();

        $this->authAdapter->resetPassword($user->getLoginId(), $password, false);

        $result = new Result();
        $result->addMessage('Temporary password successfully generated and saved');

        $eventData = null;

        switch ($mode) {
            case self::RESET_PASSWORD_BY_POST:
                // send a letter with the temp password
                $result->merge(
                    $this->sendLetter($licence, $password)
                );
                $eventData = 'By post';
                break;
            case self::RESET_PASSWORD_BY_EMAIL:
                // send temporary password email
                $result->merge(
                    $this->handleSideEffect(
                        SendUserTemporaryPasswordDto::create(
                            [
                                'user' => $user->getId(),
                                'password' => $password,
                            ]
                        )
                    )
                );
                $eventData = 'By email';
                break;
        }

        // create event history record
        $eventHistory = new EventHistory(
            $this->getUser(),
            $this->getRepo('EventHistoryType')->fetchOneByEventCode(EventHistoryType::EVENT_CODE_PASSWORD_RESET),
            $eventData
        );
        $eventHistory->setAccount($user);
        $eventHistory->setEntityType('user');
        $eventHistory->setEntityPk($user->getId());

        $this->getRepo('EventHistory')->save($eventHistory);

        return $result;
    }

    /**
     * Sends letter with a temporary password
     *
     * @param Licence $licence  Licence
     * @param string  $password Password
     *
     * @return Result
     */
    private function sendLetter(Licence $licence, string $password): Result
    {
        $template = 'SELF_SERVICE_NEW_PASSWORD';

        $queryData = [
            'licence' => $licence->getId()
        ];

        $knownValues = [
            'SELF_SERVICE_PASSWORD' => $password,
        ];

        $documentId = $this->generateDocument($template, $queryData, $knownValues);

        $printQueue = EnqueueFileCommand::create(
            [
                'documentId' => $documentId,
                'jobName' => 'New temporary password'
            ]
        );

        return $this->handleSideEffect($printQueue);
    }

    /**
     * Generates a reset password document and returns its id
     *
     * @param string $template    Template
     * @param array  $queryData   Query data
     * @param array  $knownValues Known values
     *
     * @return int
     */
    private function generateDocument(string $template, array $queryData, array $knownValues): int
    {
        $dtoData = [
            'template' => $template,
            'query' => $queryData,
            'knownValues' => $knownValues,
            'description' => 'Reset password letter',
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_APPLICATION_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        return (int)$result->getId('document');
    }

    /**
     * @return void
     * @throws ClientException
     */
    private function updateAuthService(User $newUser, ?string $previousEmailAddress, bool $previouslyDisabled): void
    {
        if ($newUser->getContactDetails()->getEmailAddress() !== $previousEmailAddress) {
            $this->authAdapter->changeAttribute($newUser->getLoginId(), 'email', $newUser->getContactDetails()->getEmailAddress());
        }

        if (!$previouslyDisabled && $newUser->isDisabled()) {
            $this->authAdapter->disableUser($newUser->getLoginId());
        } elseif ($previouslyDisabled && !$newUser->isDisabled()) {
            $this->authAdapter->enableUser($newUser->getLoginId());
        }
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->authAdapter = $container->get(ValidatableAdapterInterface::class);
        $this->passwordService = $container->get(PasswordService::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
