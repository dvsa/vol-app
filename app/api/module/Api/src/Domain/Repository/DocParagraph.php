<?php

/**
 * DocParagraph
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Doc\DocParagraph as Entity;

/**
 * DocParagraph
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocParagraph extends AbstractRepository
{
    protected $entity = Entity::class;
}
