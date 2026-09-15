<?php

namespace Dvsa\Olcs\Api\Entity\Generic;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationValidation Entity
 */
#[ORM\Table(name: 'application_validation')]
#[ORM\Entity]
class ApplicationValidation extends AbstractApplicationValidation
{
}
