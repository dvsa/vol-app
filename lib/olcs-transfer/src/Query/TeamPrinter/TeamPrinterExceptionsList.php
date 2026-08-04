<?php

/**
 * Get a list of Printer Exceptions
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\TeamPrinter;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/printer-exception")
 */
final class TeamPrinterExceptionsList extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $team;

    /**
     * ID
     * @return int
     */
    public function getTeam()
    {
        return $this->team;
    }
}
