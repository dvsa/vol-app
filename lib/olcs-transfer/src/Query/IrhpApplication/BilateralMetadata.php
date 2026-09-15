<?php

/**
 * Bilateral metadata
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;

/**
 * @Transfer\RouteName("backend/irhp-application/bilateral-metadata")
 */
class BilateralMetadata extends AbstractQuery
{
    use IrhpApplicationOptional;
}
