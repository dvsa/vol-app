<?php

/**
 * Update IRHP Permit Stock
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\CountryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\EmissionsCategory;
use Dvsa\Olcs\Transfer\FieldType\Traits\HiddenSs;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockInitialStock;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockValidFrom;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockValidTo;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\FieldType\Traits\PeriodNameKeyOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\PermitCategoryOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-permit-stock/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use IrhpPermitStockInitialStock;
    use IrhpPermitType;
    use IrhpPermitStockValidFrom;
    use IrhpPermitStockValidTo;
    use EmissionsCategory;
    use CountryOptional;
    use PermitCategoryOptional;
    use PeriodNameKeyOptional;
    use HiddenSs;
}
