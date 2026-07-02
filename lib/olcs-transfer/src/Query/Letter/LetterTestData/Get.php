<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterTestData;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/letter/letter-test-data/single")
 */
final class Get extends AbstractQuery
{
    use Identity;
}
