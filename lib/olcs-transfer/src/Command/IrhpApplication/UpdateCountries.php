<?php

/**
 * Update countries
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * @Transfer\RouteName("backend/irhp-application/single/countries")
 * @Transfer\Method("PUT")
 */
class UpdateCountries extends AbstractCommand
{
    use Traits\Identity;
    use Traits\Countries;
}
