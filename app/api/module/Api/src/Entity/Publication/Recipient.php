<?php

namespace Dvsa\Olcs\Api\Entity\Publication;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception;

/**
 * Recipient Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="recipient",
 *    indexes={
 *        @ORM\Index(name="ix_recipient_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_recipient_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_recipient_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Recipient extends AbstractRecipient
{
    public const ERROR_INVALID_SUBSCRIPTION = 'PUB-REC-1';

    public function __construct($isObjector, $contactName, $emailAddress, $sendAppDecision, $sendNoticesProcs)
    {
        $this->update($isObjector, $contactName, $emailAddress, $sendAppDecision, $sendNoticesProcs);
    }

    public function update($isObjector, $contactName, $emailAddress, $sendAppDecision, $sendNoticesProcs)
    {
        $this->validate($isObjector, $contactName, $emailAddress, $sendAppDecision, $sendNoticesProcs);

        $this->isObjector = $isObjector;
        $this->contactName = $contactName;
        $this->emailAddress = $emailAddress;
        $this->sendAppDecision = $sendAppDecision;
        $this->sendNoticesProcs = $sendNoticesProcs;
    }

    public function validate($isObjector, $contactName, $emailAddress, $sendAppDecision, $sendNoticesProcs)
    {
        $errors = [];

        // extra validation
        if ($sendAppDecision === 'N' && $sendNoticesProcs === 'N') {
            $errors[] = [
                self::ERROR_INVALID_SUBSCRIPTION => 'Subscription details must be provided'
            ];
        }

        if (empty($errors)) {
            return true;
        }

        throw new Exception\ValidationException($errors);
    }
}
