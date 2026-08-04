<?php

/**
 * Update IRFO Permit Stock Issued
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * @Transfer\RouteName("backend/irfo/permit-stock/issued")
 * @Transfer\Method("PUT")
 */
final class UpdateIrfoPermitStockIssued extends AbstractCommand
{
    use Traits\Ids;
    use Traits\IrfoGvPermit;
}
