<?php

/**
 * Create User
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserCreated as SendUserCreatedDto;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserTemporaryPassword as SendUserTemporaryPasswordDto;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\User\CreateUser as CreateUserCommand;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Mockery\Generator\StringManipulation\Pass\Pass;

/**
 * Create User
 */
final class CreateUser extends AbstractUserCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'User';

    protected $extraRepos = ['Application', 'ContactDetails', 'Licence'];

    /**
     * CreateUser constructor.
     */
    public function __construct(private PasswordService $passwordService, private ?ValidatableAdapterInterface $adapter)
    {
    }


    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        assert($command instanceof CreateUserCommand);

        if (!$this->isGranted(Permission::CAN_MANAGE_USER_INTERNAL)) {
            throw new ForbiddenException('You do not have permission to manage the record');
        }
        /** @var CreateUserCommand $command */

        $data = $command->getArrayCopy();

        // validate username
        $this->validateUsername($data['loginId']);

        // validate roles
        $this->validateRoles($data['roles']);

        if (($command->getUserType() === User::USER_TYPE_OPERATOR) && (!empty($data['licenceNumber']))) {
            // fetch licence by licence number
            $licence = $this->getRepo('Licence')->fetchByLicNo($data['licenceNumber']);

            // link with the organisation
            $data['organisations'] = [$licence->getOrganisation()];
        } elseif (($command->getUserType() === User::USER_TYPE_TRANSPORT_MANAGER) && (!empty($data['application']))) {
            // fetch application by id
            $application = $this->getRepo('Application')->fetchWithLicenceAndOrg($data['application']);

            // link with the organisation
            $data['organisations'] = [$application->getLicence()->getOrganisation()];
        }

        if (
            in_array($command->getUserType(), [User::USER_TYPE_OPERATOR, User::USER_TYPE_TRANSPORT_MANAGER])
            && (empty($data['organisations']))
        ) {
            throw new ValidationException(['New user must belong to an organisation']);
        }

        $user = User::create(
            $this->generatePid($command->getLoginId()),
            $command->getUserType(),
            $this->getRepo()->populateRefDataReference($data)
        );

        // create new contact details
        $user->setContactDetails(
            ContactDetails::create(
                $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_USER),
                $this->getRepo('ContactDetails')->populateRefDataReference(
                    $command->getContactDetails()
                )
            )
        );

        $this->getRepo()->save($user);

        $password = $this->passwordService->generatePassword();
        $realm = PasswordService::REALM_SELFSERVE;

        if ($user->getUserType() === User::USER_TYPE_INTERNAL) {
            $realm = PasswordService::REALM_INTERNAL;
        }

        try {
            $this->storeUserInAuthService($command, $password, $realm);
        } catch (ClientException $e) {
            $this->getRepo()->delete($user);
            throw new \Exception("Unable to store user in Auth Service", $e->getCode(), $e);
        }

        try {
            // send welcome email
            $this->handleSideEffect(
                SendUserCreatedDto::create(
                    [
                        'user' => $user->getId(),
                    ]
                )
            );

            // send temporary password email
            $this->handleSideEffect(
                SendUserTemporaryPasswordDto::create(
                    [
                        'user' => $user->getId(),
                        'password' => $password,
                    ]
                )
            );
        } catch (\Exception) {
            // swallow any exception
        }

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('User created successfully');

        return $result;
    }

    /**
     * @return string
     */
    private function generatePid(string $loginId)
    {
        return null;
    }

    /**
     * @throws ClientException
     */
    private function storeUserInAuthService(CreateUserCommand $command, string &$password, string $realm)
    {
        $this->adapter->register(
            $command->getLoginId(),
            $password,
            $command->getContactDetails()['emailAddress']
        );
    }
}
