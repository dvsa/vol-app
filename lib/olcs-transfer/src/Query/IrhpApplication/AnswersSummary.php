<?php

/**
 * Answers summary
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TranslateToWelshOptional;

/**
 * @Transfer\RouteName("backend/irhp-application/answers-summary")
 */
class AnswersSummary extends AbstractQuery
{
    use Identity;
    use IrhpPermitApplicationOptional;
    use TranslateToWelshOptional;
}
