<?php

/**
 * Delete one or more OrganisationPerson
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\OrganisationPerson;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/organisation-person")
 * @Transfer\Method("DELETE")
 */
final class DeleteList extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * Get TM Employment ID's
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
