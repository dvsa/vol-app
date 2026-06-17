<?php

/**
 * Update Fee Type
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Command\FeeType;

use Dvsa\Olcs\Transfer\FieldType\Traits\AnnualValue;
use Dvsa\Olcs\Transfer\FieldType\Traits\EffectiveFrom;
use Dvsa\Olcs\Transfer\FieldType\Traits\FiveYearValue;
use Dvsa\Olcs\Transfer\FieldType\Traits\FixedValue;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/fee-type/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use EffectiveFrom;
    use FixedValue;
    use AnnualValue;
    use FiveYearValue;
}
