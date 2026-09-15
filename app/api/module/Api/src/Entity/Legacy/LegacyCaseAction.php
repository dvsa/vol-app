<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Doctrine\ORM\Mapping as ORM;

/**
 * LegacyCaseAction Entity
 */
#[ORM\Table(name: 'legacy_case_action')]
#[ORM\Entity]
class LegacyCaseAction extends AbstractLegacyCaseAction
{
}
