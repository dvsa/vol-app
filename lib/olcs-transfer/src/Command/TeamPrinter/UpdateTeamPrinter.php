<?php

/**
 * Update Team Printer
 */

namespace Dvsa\Olcs\Transfer\Command\TeamPrinter;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\UserOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;

/**
 * @Transfer\RouteName("backend/printer-exception/single")
 * @Transfer\Method("PUT")
 */
final class UpdateTeamPrinter extends AbstractCommand
{
    use SubCategoryOptional;
    use UserOptional;
    use Identity;
    use Version;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $team;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $printer;

    public function getTeam()
    {
        return $this->team;
    }

    public function getPrinter()
    {
        return $this->printer;
    }
}
