<?php

/**
 * Delete Workshops
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Workshop;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;

/**
 * @Transfer\RouteName("backend/workshop")
 * @Transfer\Method("DELETE")
 */
final class DeleteWorkshop extends AbstractCommand
{
    use LicenceOptional;
    use ApplicationOptional;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
