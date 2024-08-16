<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Messaging\MessagingSubject as Entity;

class MessagingSubject extends AbstractRepository
{
    protected $entity = Entity::class;
}
