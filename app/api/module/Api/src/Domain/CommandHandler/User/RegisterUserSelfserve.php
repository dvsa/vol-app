<?php

/**
 * Register User Selfserve
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserRegistered as SendUserRegisteredDto;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserTemporaryPassword as SendUserTemporaryPasswordDto;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterUserSelfserveCommand;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

/**
 * Register User Selfserve
 */
final class RegisterUserSelfserve extends AbstractUserCommandHandler implements
    TransactionedInterface
{
    protected $repoServiceName = 'User';

    protected $extraRepos = ['ContactDetails', 'Licence', 'Organisation'];


    public function __construct(private readonly PasswordService $passwordService, private readonly ?ValidatableAdapterInterface $adapter)
    {
    }

    public function handleCommand(CommandInterface $command)
    {
        assert($command instanceof RegisterUserSelfserveCommand);

        $data = $command->getArrayCopy();

        // validate username
        $this->validateUsername($data['loginId']);

        // link with organisations
        if (!empty($data['licenceNumber'])) {
            // fetch licence by licence number
            $licence = $this->getRepo('Licence')->fetchForUserRegistration($data['licenceNumber']);

            // link with the organisation
            $data['organisations'] = [$licence->getOrganisation()];
        } elseif (!empty($data['organisationName'])) {
            // create organisation and link with it
            $data['organisations'] = [$this->createOrganisation($data)];
        }

        if (empty($data['organisations'])) {
            // new user has to be linked to an organisation
            throw new BadRequestException('User must be linked to an organisation');
        }

        // register new user as an operator admin
        $data['roles'] = User::getRolesByUserType(User::USER_TYPE_OPERATOR, User::PERMISSION_ADMIN);

        $user = User::create(
            $this->generatePid($command->getLoginId()),
            User::USER_TYPE_OPERATOR,
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

        $result = new Result();

        $this->getRepo()->save($user);

        $password = $this->passwordService->generatePassword();

        try {
            $this->storeUserInAuthService($command, $password);
        } catch (ClientException $e) {
            $this->getRepo()->delete($user);
            throw new \Exception("Unable to store user in Auth Service", $e->getCode(), $e);
        }

        try {
            if (isset($licence)) {
                // for the current licence holder a letter with generated password needs to be sent
                $result->merge(
                    $this->sendLetter($licence, $password)
                );
            } else {
                // send welcome email
                $this->handleSideEffect(
                    SendUserRegisteredDto::create(
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
            }
        } catch (\Exception) {
            // swallow any exception
        }

        $result->addId('user', $user->getId());
        $result->addMessage('User created successfully');

        return $result;
    }

    private function createOrganisation($data)
    {
        // create new organisation
        $organisation = new OrganisationEntity();
        $organisation->setType(
            $this->getRepo()->getRefdataReference($data['businessType'])
        );
        $organisation->setName($data['organisationName']);
        $organisation->setAllowEmail('Y');

        // save
        $this->getRepo('Organisation')->save($organisation);

        return $organisation;
    }

    private function sendLetter(LicenceEntity $licence, $password)
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

    private function generateDocument($template, $queryData, $knownValues)
    {
        $dtoData = [
            'template' => $template,
            'query' => $queryData,
            'knownValues' => $knownValues,
            'description' => 'Self service new password letter',
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_APPLICATION_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        return $result->getId('document');
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
    private function storeUserInAuthService(RegisterUserSelfserveCommand $command, string &$password)
    {
        $this->adapter->register(
            $command->getLoginId(),
            $password,
            $command->getContactDetails()['emailAddress']
        );
    }
}
