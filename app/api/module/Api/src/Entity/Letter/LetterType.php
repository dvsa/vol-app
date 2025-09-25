<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_type")
 */
class LetterType extends AbstractLetterType
{
}