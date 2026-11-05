<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

/**
 * Create or update the SMS number on a licence's correspondence contact.
 * Raises no Address Change task.
 */
final class UpdateSmsNumber extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['PhoneContact'];

    private EventHistoryCreator $eventHistoryCreator;

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $licence = $this->getRepo()->fetchUsingId($command);

        $number = SmsPhoneNumber::normalise((string) $command->getSmsNumber());
        if ($number === null) {
            throw new ValidationException(['smsNumber' => ['contact-number-sms-invalid']]);
        }

        $contactDetails = $licence->getCorrespondenceCd();
        if ($contactDetails === null) {
            // Nowhere to keep it, but that must not stop the continuation.
            Logger::warn('SMS number not saved, licence has no correspondence address', ['licence' => $licence->getId()]);
            $this->result->addMessage('SMS number not saved, licence has no correspondence address');

            return $this->result;
        }

        $contact = null;
        foreach ($contactDetails->getPhoneContacts() as $existing) {
            if ($existing->getPhoneContactType()->getId() === PhoneContact::TYPE_SMS) {
                $contact = $existing;
                break;
            }
        }

        $isUpdate = $contact !== null && $contact->getPhoneNumber() !== $number;

        if ($contact === null) {
            $contact = new PhoneContact($this->getRepo()->getRefdataReference(PhoneContact::TYPE_SMS));
            $contact->setContactDetails($contactDetails);
            $contactDetails->getPhoneContacts()->add($contact);
            $this->result->addMessage('SMS number created');
        } else {
            $this->result->addMessage('SMS number updated');
        }

        $contact->setPhoneNumber($number);
        $this->getRepo('PhoneContact')->save($contact);

        // same history as a phone change through SaveAddresses
        if ($isUpdate) {
            $this->eventHistoryCreator->create(
                $contact,
                EventHistoryType::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS,
                null,
                $licence
            );
        }

        return $this->result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');

        return parent::__invoke($container, $requestedName, $options);
    }
}
