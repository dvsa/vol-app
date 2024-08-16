<?php

namespace Dvsa\Olcs\Api\Entity\PrintScan;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;

/**
 * TeamPrinter Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="team_printer",
 *    indexes={
 *        @ORM\Index(name="ix_team_printer_printer_id", columns={"printer_id"}),
 *        @ORM\Index(name="ix_team_printer_team_id", columns={"team_id"})
 *    }
 * )
 */
class TeamPrinter extends AbstractTeamPrinter
{
    public const ERROR_PRINTER_EXCEPTION_EXISTS = 'err_printer_exception_exist';

    /**
     * Constructor for TeamPrinter
     */
    public function __construct(TeamEntity $team, PrinterEntity $printer)
    {
        $this->setTeam($team);
        $this->setPrinter($printer);
    }
}
