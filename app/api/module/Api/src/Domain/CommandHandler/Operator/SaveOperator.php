<?php

/**
 * Save Operator
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Operator;

use Dvsa\Olcs\Api\Domain\Command\Organisation\ChangeBusinessType;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson as OrganisationPersonEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress as SaveAddressCmd;
use Doctrine\ORM\Query;

/**
 * Save Operator
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class SaveOperator extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Organisation';

    protected $extraRepos = ['OrganisationPerson', 'Person'];

    public const string ERROR_UNKNOWN_TYPE = 'ERR_ORG_1';

    public function handleCommand(CommandInterface $command)
    {
        $this->validateOrganisation($command);
        $type = $command->getBusinessType();

        if ($command instanceof \Dvsa\Olcs\Transfer\Command\Operator\Update) {
            /** @var OrganisationEntity $organisation */
            $organisation = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
            $message = 'Organisation updated successfully';
        } else {
            /** @var OrganisationEntity $organisation */
            $organisation = new OrganisationEntity();
            $message = 'Organisation created successfully';
        }
        $this->updateOrganisation($command, $organisation);

        if ($type === OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY || $type === OrganisationEntity::ORG_TYPE_LLP) {
            $this->saveAddress($command, $organisation);
        }

        $this->getRepo()->save($organisation);

        if ($type === OrganisationEntity::ORG_TYPE_SOLE_TRADER) {
            $this->saveOrganisationPerson($command, $organisation);
        }

        $result = new Result();
        $result->addId('organisation', $organisation->getId());
        $result->addMessage($message);
        $result->merge(
            $this->clearOrganisationCacheSideEffect($organisation->getId())
        );

        return $result;
    }

    private function saveAddress($command, $organisation)
    {
        $address = $command->getAddress();
        $address['contactType'] = AddressEntity::CONTACT_TYPE_REGISTERED_ADDRESS;
        $result = $this->handleSideEffect(
            SaveAddressCmd::create($address)
        );
        $contactDetailsId = $result->getId('contactDetails');
        if ($contactDetailsId !== null) {
            $organisation->setContactDetails(
                $this->getRepo()->getReference(ContactDetailsEntity::class, $contactDetailsId)
            );
        }
    }

    private function saveOrganisationPerson($command, $organisation)
    {
        $person = $this->createPersonObject($command);
        $this->getRepo('Person')->save($person);
        if ($command instanceof \Dvsa\Olcs\Transfer\Command\Operator\Create || !$command->getPersonId()) {
            $organisationPerson = new OrganisationPersonEntity();
            $organisationPerson->setPerson($person);
            $organisationPerson->setOrganisation($organisation);
            $this->getRepo('OrganisationPerson')->save($organisationPerson);
        }
    }

    /**
     * Create person object
     * @param $command
     * @return PersonEntity
     */
    private function createPersonObject($command)
    {
        if ($command instanceof \Dvsa\Olcs\Transfer\Command\Operator\Create || !$command->getPersonId()) {
            $person = new PersonEntity();
        } else {
            $person = $this->getRepo('Person')->fetchById(
                $command->getPersonId(),
                Query::HYDRATE_OBJECT,
                $command->getPersonVersion()
            );
        }
        $person->updatePerson(
            $command->getFirstName(),
            $command->getLastName()
        );

        return $person;
    }

    /**
     * @param $command
     * @param $organisation OrganisationEntity
     * @return OrganisationEntity
     */
    private function updateOrganisation($command, $organisation)
    {
        $businessType = $this->getRepo()->getRefdataReference($command->getBusinessType());

        $cpid = $this->getRepo()->getRefdataReference($command->getCpid());

        if ($this->updatingBusinessType($organisation, $businessType)) {
            $data = [
                'id' => $organisation->getId(),
                'businessType' => $command->getBusinessType(),
                'confirm' => $command->getConfirm()
            ];

            $this->handleSideEffect(ChangeBusinessType::create($data));
        } else {
            $organisation->setType($businessType);
        }

        $organisation->updateOrganisation(
            $command->getName(),
            $command->getCompanyNumber(),
            $command->getFirstName(),
            $command->getLastName(),
            $command->getIsIrfo(),
            $command->getNatureOfBusiness(),
            $cpid,
            $command->getAllowEmail()
        );
        return $organisation;
    }

    protected function updatingBusinessType($organisation, $businessType)
    {
        // We may be creating an org, so check if we have a business type set
        if ($organisation->getType() === null) {
            return false;
        }

        return ($organisation->getType() !== $businessType);
    }

    protected function validateOrganisation($command)
    {
        $type = $command->getBusinessType();
        match ($type) {
            OrganisationEntity::ORG_TYPE_PARTNERSHIP, OrganisationEntity::ORG_TYPE_OTHER => $this->validatePartnershipOrOther($command),
            OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY, OrganisationEntity::ORG_TYPE_LLP => $this->validateRegOrLlp($command),
            OrganisationEntity::ORG_TYPE_SOLE_TRADER => $this->validateSoleTrader($command),
            OrganisationEntity::ORG_TYPE_IRFO => $this->validateIrfo($command),
            default => throw new ValidationException(
                [self::ERROR_UNKNOWN_TYPE => 'Unknown business type']
            ),
        };
    }

    protected function validatePartnershipOrOther($command)
    {
        $errors = [];
        if (!$command->getName()) {
            $errors['name'][] = 'Operator name is required';
        }
        if (!$command->getNatureOfBusiness()) {
            $errors['natureOfBusiness'][] = 'Nature of Business is required';
        }
        if (count($errors)) {
            throw new ValidationException($errors);
        }
    }

    protected function validateRegOrLlp($command)
    {
        $errors = [];
        if (!$command->getCompanyNumber()) {
            $errors['companyNumber'][] = 'Company Number is required';
        }
        if (!$command->getName()) {
            $errors['name'][] = 'Operator Name is required';
        }
        if (!$command->getNatureOfBusiness()) {
            $errors['natureOfBusiness'][] = 'Nature of Business is required';
        }
        if (count($errors)) {
            throw new ValidationException($errors);
        }
    }

    protected function validateSoleTrader($command)
    {
        $errors = [];
        if (!$command->getNatureOfBusiness()) {
            $errors['natureOfBusiness'][] = 'Nature of Business is required';
        }
        if (!$command->getLastName()) {
            $errors['lastName'][] = 'Last name is required';
        }
        if (count($errors)) {
            throw new ValidationException($errors);
        }
    }

    protected function validateIrfo($command)
    {
        $errors = [];
        if (!$command->getName()) {
            $errors['name'][] = 'Operator Name is required';
        }
        if (count($errors)) {
            throw new ValidationException($errors);
        }
    }
}
