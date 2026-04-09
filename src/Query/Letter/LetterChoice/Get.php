<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterChoice;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/letter/letter-choice/single")
 */
final class Get extends AbstractQuery
{
    use Identity;
}
