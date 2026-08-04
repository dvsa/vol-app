<?php

/**
 * Create an IRHP Permit Stock
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationPathGroupOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusinessProcess;
use Dvsa\Olcs\Transfer\FieldType\Traits\CountryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\EmissionsCategory;
use Dvsa\Olcs\Transfer\FieldType\Traits\HiddenSs;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockInitialStock;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockValidFrom;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockValidTo;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\FieldType\Traits\PeriodNameKeyOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\PermitCategoryOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-permit-stock")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use IrhpPermitStockInitialStock;
    use IrhpPermitStockValidFrom;
    use EmissionsCategory;
    use IrhpPermitStockValidTo;
    use IrhpPermitType;
    use CountryOptional;
    use PermitCategoryOptional;
    use ApplicationPathGroupOptional;
    use BusinessProcess;
    use PeriodNameKeyOptional;
    use HiddenSs;
}
