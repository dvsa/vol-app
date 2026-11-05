<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\DeleteContactDetailsAndAddressTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Service\Sms\SmsNumber;
use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;
use Psr\Container\ContainerInterface;

/**
 * Save LVA Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class SaveAddresses extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use DeleteContactDetailsAndAddressTrait;
    use CacheAwareTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['ContactDetails', 'PhoneContact'];

    private $phoneTypes = [
        'primary' => PhoneContact::TYPE_PRIMARY,
        'secondary' => PhoneContact::TYPE_SECONDARY,
        'sms' => PhoneContact::TYPE_SMS,
    ];

    private EventHistoryCreator $eventHistoryCreator;

    /**
     * Handle command
     *
     * @param DomainCmd\Licence\SaveAddresses $command Command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($command);

        $contact = $command->getContact();
        $smsData = $this->normaliseSms($contact);

        $this->result->setFlag('isDirty', false);

        $this->saveCorrespondenceAddress($command, $licence);

        $this->updateCorrespondencePhoneContacts($smsData, $licence);

        $this->maybeSaveEstablishmentAddress($command, $licence);

        $this->maybeAddOrRemoveTransportConsultant($command, $licence);

        $this->getRepo()->save($licence);
        $this->clearLicenceCaches($licence);

        return $this->result;
    }

    /**
     * Normalise phone_sms in the contact data, or throw when it is not a valid UK mobile or landline
     *
     * @param array|null $contact Contact data
     *
     * @return array
     * @throws ValidationException
     */
    private function normaliseSms($contact): array
    {
        $contact = (array) $contact;

        if (isset($contact['phone_sms']) && trim((string) $contact['phone_sms']) !== '') {
            $normalised = SmsPhoneNumber::normalise((string) $contact['phone_sms']);

            if ($normalised === null) {
                throw new ValidationException(['phone_sms' => ['contact-number-sms-invalid']]);
            }

            $contact['phone_sms'] = $normalised;
        } elseif (array_key_exists('phone_sms', $contact)) {
            $contact['phone_sms'] = '';
        }

        return $contact;
    }

    /**
     * Handle side effect
     *
     * @param Result $result Temp result
     *
     * @return void
     */
    private function handleSideEffectResult(Result $result)
    {
        $this->result->merge($result);

        if ($result->getFlag('hasChanged')) {
            $this->result->setFlag('isDirty', true);
        }
    }

    /**
     * Save Corr address
     *
     * @param DomainCmd\Licence\SaveAddresses $command Comman
     * @param Licence                         $licence Licence entity
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function saveCorrespondenceAddress(DomainCmd\Licence\SaveAddresses $command, Licence $licence)
    {
        $address = $command->getCorrespondenceAddress();
        $address['contactType'] = ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS;
        $result = $this->handleSideEffect(
            DomainCmd\ContactDetails\SaveAddress::create($address)
        );

        if ($result->getId('contactDetails') !== null) {
            $licence->setCorrespondenceCd(
                $this->getRepo()->getReference(ContactDetails::class, $result->getId('contactDetails'))
            );
        }

        $correspondenceCd = $licence->getCorrespondenceCd();

        $correspondenceCd->setFao($command->getCorrespondence()['fao'] ?? null);
        $correspondenceCd->setEmailAddress($command->getContact()['email'] ?? null);

        $version = $correspondenceCd->getVersion();
        $this->getRepo('ContactDetails')->save($correspondenceCd);

        if ($correspondenceCd->getVersion() != $version) {
            $result->setFlag('hasChanged', true);
            $result->addMessage('Contact details updated');

            // create Event History record
            $this->eventHistoryCreator->create($correspondenceCd, EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS, null, $licence);
        }

        $this->handleSideEffectResult($result);
    }

    /**
     * Update Corr Phone contacts
     *
     * @param array   $contact Contact data, phone_sms already normalised
     * @param Licence $licence Licence entity
     *
     * @return void
     */
    private function updateCorrespondencePhoneContacts(array $contact, Licence $licence)
    {
        $this->updatePhoneContacts(
            $contact,
            $licence->getCorrespondenceCd(),
            $licence,
            true
        );
    }

    /**
     * Common functionality to update phone contacts
     *
     * @param array          $data           Phone Contact data
     * @param ContactDetails $contactDetails Contact Details entity
     * @param Licence $licence entity
     * @param bool $includeSms Handle the SMS number too (correspondence contact only)
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function updatePhoneContacts($data, ContactDetails $contactDetails, ?Licence $licence = null, bool $includeSms = false)
    {
        foreach ($this->phoneTypes as $phoneType => $phoneRefName) {
            $isSms = $phoneType === 'sms';

            // a post without the SMS key leaves any existing SMS row alone
            if ($isSms && (!$includeSms || !array_key_exists('phone_sms', $data))) {
                continue;
            }

            $result = new Result();
            $result->setFlag('hasChanged', false);

            $version = null;
            /** @var PhoneContact $contact */
            $contact = null;

            if (!empty($data['phone_' . $phoneType . '_id'])) {
                $version = $data['phone_' . $phoneType . '_version'];

                $contact = $this->getRepo('PhoneContact')->fetchById(
                    $data['phone_' . $phoneType . '_id'],
                    Query::HYDRATE_OBJECT,
                    $version
                );
            } elseif ($isSms && !empty($data['phone_sms'])) {
                // a form loaded before the SMS row existed posts no id, so reuse the row instead of adding another
                $contact = SmsNumber::latestContact($contactDetails);
                $version = $contact?->getVersion();
            }

            $hasContact = ($contact instanceof PhoneContact);

            if (!empty($data['phone_' . $phoneType])) {
                if (!$hasContact) {
                    $contact = new PhoneContact(
                        $this->getRepo()->getRefdataReference($phoneRefName)
                    );
                    $contact->setContactDetails($contactDetails);

                    $contactDetails->getPhoneContacts()->add($contact);
                }

                $contact->setPhoneNumber($data['phone_' . $phoneType]);

                $this->getRepo('PhoneContact')->save($contact);

                if ($version === null) {
                    $result->addMessage('Phone contact ' . $phoneType . ' created');
                    $result->setFlag('hasChanged', true);
                } elseif ($contact->getVersion() != $version) {
                    $result->addMessage('Phone contact ' . $phoneType . ' updated');
                    $result->setFlag('hasChanged', true);

                    // create Event History record
                    $this->eventHistoryCreator->create($contact, EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS, null, $licence);
                }
            } elseif ($isSms) {
                // clearing the number removes every SMS row, including any older duplicates
                $rows = SmsNumber::smsContacts($contactDetails);
                if ($hasContact && !in_array($contact, $rows, true)) {
                    $rows[] = $contact;
                }

                foreach ($rows as $row) {
                    if ($row->getId() > 0) {
                        $contactDetails->getPhoneContacts()->removeElement($row);
                        $this->getRepo('PhoneContact')->delete($row);
                        $result->setFlag('hasChanged', true);
                    }
                }

                if ($result->getFlag('hasChanged')) {
                    $result->addMessage('Phone contact sms deleted');
                }
            } elseif ($hasContact && $contact->getId() > 0) {
                $contactDetails->getPhoneContacts()->removeElement($contact);

                $this->getRepo('PhoneContact')->delete($contact);

                $result->addMessage('Phone contact ' . $phoneType . ' deleted');
                $result->setFlag('hasChanged', true);
            }

            if ($isSms) {
                // SMS changes are tracked apart from isDirty so they raise no Address Change task
                foreach ($result->getMessages() as $message) {
                    $this->result->addMessage($message);
                }
                if ($result->getFlag('hasChanged')) {
                    $this->result->setFlag('smsChanged', true);
                }
                continue;
            }

            $this->handleSideEffectResult($result);
        }
    }

    /**
     * Save Establishment Address
     *
     * @param DomainCmd\Licence\SaveAddresses $command Comman
     * @param Licence                         $licence Licence Entity
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function maybeSaveEstablishmentAddress(DomainCmd\Licence\SaveAddresses $command, Licence $licence)
    {
        if (empty($command->getEstablishmentAddress())) {
            return;
        }

        $address = $command->getEstablishmentAddress();
        $address['contactType'] = ContactDetails::CONTACT_TYPE_ESTABLISHMENT_ADDRESS;
        $result = $this->handleSideEffect(
            DomainCmd\ContactDetails\SaveAddress::create($address)
        );

        if ($result->getId('contactDetails') !== null) {
            $licence->setEstablishmentCd(
                $this->getRepo()->getReference(ContactDetails::class, $result->getId('contactDetails'))
            );
        }

        $this->handleSideEffectResult($result);
    }

    /**
     * Add or Remove Transport Consultant
     *
     * @param DomainCmd\Licence\SaveAddresses $command Command
     * @param Licence                         $licence Licence Entity
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function maybeAddOrRemoveTransportConsultant(DomainCmd\Licence\SaveAddresses $command, Licence $licence)
    {
        if (empty($command->getConsultant())) {
            return;
        }

        $params = $command->getConsultant();

        $result = new Result();

        $result->setFlag('hasChanged', false);

        if ($params['add-transport-consultant'] === 'Y') {
            $address = $params['address'];
            $address['contactType'] = ContactDetails::CONTACT_TYPE_TRANSPORT_CONSULTANT;
            $result = $this->handleSideEffect(
                DomainCmd\ContactDetails\SaveAddress::create($address)
            );

            $this->handleSideEffectResult($result);

            if ($result->getId('contactDetails') !== null) {
                $licence->setTransportConsultantCd(
                    $this->getRepo()->getReference(ContactDetails::class, $result->getId('contactDetails'))
                );
            }

            $transportConsultant = $licence->getTransportConsultantCd();

            $version = $transportConsultant->getVersion();

            $transportConsultant->setFao($params['transportConsultantName']);
            $transportConsultant->setWrittenPermissionToEngage($params['writtenPermissionToEngage']);
            $transportConsultant->setEmailAddress($params['contact']['email']);

            $this->getRepo('ContactDetails')->save($transportConsultant);

            if ($transportConsultant->getVersion() != $version) {
                $result->setFlag('hasChanged', true);
                $result->addMessage('Transport consultant updated');
            }

            $this->updatePhoneContacts($params['contact'], $transportConsultant);
        } elseif ($licence->getTransportConsultantCd()) {
            $this->maybeDeleteContactDetailsAndAddress($licence->getTransportConsultantCd());
            $licence->setTransportConsultantCd(null);
            $result->setFlag('hasChanged', true);
            $result->addMessage('Transport consultant deleted');
        }

        $this->handleSideEffectResult($result);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($container, $requestedName, $options);
    }
}
