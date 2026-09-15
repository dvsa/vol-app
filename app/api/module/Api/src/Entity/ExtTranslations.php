<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExtTranslations Entity
 */
#[ORM\Table(name: 'ext_translations')]
#[ORM\Entity]
class ExtTranslations extends AbstractExtTranslations
{
}
