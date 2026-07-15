<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterChoice Entity
 */
#[ORM\Table(name: 'letter_choice')]
#[ORM\Entity]
class LetterChoice extends AbstractLetterChoice
{
}
