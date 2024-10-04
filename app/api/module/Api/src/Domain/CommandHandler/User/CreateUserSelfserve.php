<?php

/**
 * Create User Selfserve
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
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve as CreateUserSelfserveCommand;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

/**
 * Create User Selfserve
 */
final class CreateUserSelfserve extends AbstractUserCommandHandler implements
    AuthAwareInterface,
    TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'User';

    protected $extraRepos = ['ContactDetails'];


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
        assert($command instanceof CreateUserSelfserveCommand);

        $data = $command->getArrayCopy();

        // validate username
        $this->validateUsername($data['loginId']);

        // copy user type from the current loggedin user
        switch ($this->getCurrentUser()->getUserType()) {
            case User::USER_TYPE_PARTNER:
                $data['userType'] = User::USER_TYPE_PARTNER;
                $data['partnerContactDetails'] = $this->getCurrentUser()->getPartnerContactDetails()->getId();
                break;
            case User::USER_TYPE_LOCAL_AUTHORITY:
                $data['userType'] = User::USER_TYPE_LOCAL_AUTHORITY;
                $data['localAuthority'] = $this->getCurrentUser()->getLocalAuthority()->getId();
                break;
            case User::USER_TYPE_OPERATOR:
            case User::USER_TYPE_TRANSPORT_MANAGER:
                $data['userType'] = User::USER_TYPE_OPERATOR;
                $data['organisations'] = array_map(
                    fn($item) => $item->getOrganisation(),
                    $this->getCurrentUser()->getOrganisationUsers()->toArray()
                );
                break;
            default:
                // only available to specific user types
                throw new BadRequestException('User type must be provided');
        }

        // populate roles based on the user type and permission
        $data['roles'] = User::getRolesByUserType($data['userType'], $data['permission']);

        $user = User::create(
            $this->generatePid($command->getLoginId()),
            $data['userType'],
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
        try {
            $this->storeUserInAuthService($command, $password);
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
     *
     */
    private function generatePid(string $loginId)
    {
        return null;
    }

    /**
     * @throws ClientException
     */
    private function storeUserInAuthService(CreateUserSelfserveCommand $command, string &$password)
    {
        $this->adapter->register(
            $command->getLoginId(),
            $password,
            $command->getContactDetails()['emailAddress']
        );
    }
}
