<?php

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageFailures Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="message_failures",
 *    indexes={
 *        @ORM\Index(name="ix_message_failures_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_message_failures_queue_type", columns={"queue_type"})
 *    }
 * )
 */
class MessageFailures extends AbstractMessageFailures
{
}
