<?php

/**
 * RetrievalLinkEvent
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkEvent as Entity;

/**
 * RetrievalLinkEvent
 */
class RetrievalLinkEvent extends AbstractRepository
{
    protected $entity = Entity::class;
}
