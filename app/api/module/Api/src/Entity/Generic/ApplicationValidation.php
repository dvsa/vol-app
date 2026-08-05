<?php

namespace Dvsa\Olcs\Api\Entity\Generic;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationValidation Entity
 */
#[ORM\Table(name: 'application_validation')]
#[ORM\Index(name: 'fk_application_validation_question1_idx', columns: ['question_id'])]
#[ORM\Index(name: 'fk_application_validation_application_step1_idx', columns: ['application_step_id'])]
#[ORM\Entity]
class ApplicationValidation extends AbstractApplicationValidation
{
}
