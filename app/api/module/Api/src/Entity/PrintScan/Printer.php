<?php

namespace Dvsa\Olcs\Api\Entity\PrintScan;

use Doctrine\ORM\Mapping as ORM;

/**
 * Printer Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="printer")
 */
class Printer extends AbstractPrinter
{
    public const ERROR_TEAMS_EXISTS = 'err_teams_exist';

    public function canDelete()
    {
        if (count($this->getTeamPrinters())) {
            return false;
        }
        return true;
    }
}
