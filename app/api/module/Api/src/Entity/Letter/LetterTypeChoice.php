<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTypeChoice Entity
 */
#[ORM\Table(name: 'letter_type_choice')]
#[ORM\Entity]
class LetterTypeChoice extends AbstractLetterTypeChoice
{
}
