<?php

/**
 * Delete Grace Period
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\GracePeriod;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/grace-periods")
 * @Transfer\Method("DELETE")
 */
final class DeleteGracePeriod extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @return mixed
     */
    public function getIds()
    {
        return $this->ids;
    }
}
