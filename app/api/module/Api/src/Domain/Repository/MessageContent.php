<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Messaging\MessagingContent as Entity;

class MessageContent extends AbstractRepository
{
    protected $entity = Entity::class;
}
