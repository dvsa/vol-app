<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SlaException Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="sla_exception")
 */
class SlaException extends AbstractSlaException
{
}