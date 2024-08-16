<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;

/**
 * Interface ProcessInterface
 * @package Dvsa\Olcs\Api\Service\Publication\Process
 */
interface ProcessInterface
{
    public function process(PublicationLink $publication, ImmutableArrayObject $context);
}
