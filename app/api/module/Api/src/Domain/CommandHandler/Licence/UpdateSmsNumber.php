<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;

/**
 * Create or update the SMS number on a licence's correspondence contact.
 * Raises no Address Change task.
 */
final class UpdateSmsNumber extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['PhoneContact'];

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
            throw new ValidationException(['smsNumber' => ['contact-number-sms-invalid']]);
        }

        $contact = null;
        foreach ($contactDetails->getPhoneContacts() as $existing) {
            if ($existing->getPhoneContactType()->getId() === PhoneContact::TYPE_SMS) {
                $contact = $existing;
                break;
            }
        }

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

        return $this->result;
    }
}
