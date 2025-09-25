<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PiSlaException Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="pi_sla_exception")
 */
class PiSlaException extends AbstractPiSlaException
{
}