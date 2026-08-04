<?php

namespace Dvsa\Olcs\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class Fee
 * @Transfer\RouteName("backend/fee/single")
 */
class Fee extends AbstractQuery implements FieldType\IdentityInterface
{
    use FieldTypeTraits\Identity;
}
