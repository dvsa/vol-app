<?php

/**
 * Variation Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\VariationOperatingCentre;

use Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre\AbstractOperatingCentreCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsTaOverridden;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/named-single/variation-operating-centre/single")
 * @Transfer\Method("PUT")
 */
class Update extends AbstractOperatingCentreCommand
{
    use Application;
    use Version;
    use IsTaOverridden;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
