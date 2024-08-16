<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation as Entity;

class MessagingConversation extends AbstractRepository
{
    protected $entity = Entity::class;
}
