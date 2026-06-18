<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Query to fetch a LetterInstance with rendered preview HTML
 *
 * @Transfer\RouteName("backend/letter/letter-instance/preview")
 */
final class Preview extends AbstractQuery
{
    use Identity;
}
