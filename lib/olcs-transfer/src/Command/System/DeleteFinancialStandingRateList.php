<?php

/**
 * Delete a list of FinancialStandingRates
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\System;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * @Transfer\RouteName("backend/financial-standing-rate")
 * @Transfer\Method("DELETE")
 */
class DeleteFinancialStandingRateList extends AbstractCommand
{
    use Ids;
}
