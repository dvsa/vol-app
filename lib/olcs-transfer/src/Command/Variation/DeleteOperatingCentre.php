<?php

/**
 * Delete Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/variation/named-single/operating-centre/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteOperatingCentre extends AbstractCommand
{
    use Application;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2})
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
