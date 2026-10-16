<?php

namespace Dvsa\Olcs\Api\Entity\Messaging;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessagingSubject Entity
 */
#[ORM\Table(name: 'messaging_subject')]
#[ORM\Index(name: 'fk_messaging_subject_created_by_user_id', columns: ['created_by'])]
#[ORM\Index(name: 'fk_messaging_subject_last_modified_by_user_id', columns: ['last_modified_by'])]
#[ORM\Entity]
class MessagingSubject extends AbstractMessagingSubject
{
}
