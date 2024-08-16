<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;

/**
 * Interface ContextInterface
 * @package Dvsa\Olcs\Api\Service\Publication\Context
 */
interface ContextInterface
{
    public function provide(PublicationLink $publication, \ArrayObject $context);
}
