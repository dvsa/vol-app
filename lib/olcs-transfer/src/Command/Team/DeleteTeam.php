<?php

/**
 * Delete Team
 */

namespace Dvsa\Olcs\Transfer\Command\Team;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/team/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteTeam extends AbstractDeleteCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $newTeam;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $validate;

    public function getNewTeam()
    {
        return $this->newTeam;
    }

    public function getValidate()
    {
        return $this->validate;
    }
}
