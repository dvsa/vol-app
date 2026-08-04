<?php

namespace Dvsa\Olcs\Transfer\Query\System;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Financial Standing Rate
 * @Transfer\RouteName("backend/financial-standing-rate/single")
 */
class FinancialStandingRate extends AbstractQuery implements FieldType\IdentityInterface
{
    use FieldTypeTraits\Identity;
}
