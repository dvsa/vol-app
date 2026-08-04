<?php

namespace Dvsa\Olcs\Transfer\Command\Complaint;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/complaint/single")
 * @Transfer\Method("DELETE")
 */
class DeleteComplaint extends AbstractDeleteCommand
{
    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Validator("Laminas\Validator\Identical", options={"token": true})
     */
    protected $isCompliance = true;

    /**
     * @return bool
     */
    public function getIsCompliance()
    {
        return $this->isCompliance;
    }
}
