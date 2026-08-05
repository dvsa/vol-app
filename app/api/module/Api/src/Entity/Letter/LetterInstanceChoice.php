<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterInstanceChoice Entity
 */
#[ORM\Table(name: 'letter_instance_choice')]
#[ORM\Entity]
class LetterInstanceChoice extends AbstractLetterInstanceChoice
{
}
