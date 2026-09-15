<?php

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageFailures Entity
 */
#[ORM\Table(name: 'message_failures')]
#[ORM\Index(name: 'ix_message_failures_organisation_id', columns: ['organisation_id'])]
#[ORM\Index(name: 'ix_message_failures_queue_type', columns: ['queue_type'])]
#[ORM\Entity]
class MessageFailures extends AbstractMessageFailures
{
}
