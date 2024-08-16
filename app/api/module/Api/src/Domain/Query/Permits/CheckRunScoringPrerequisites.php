<?php

/**
 * Check run scoring prerequisites
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

class CheckRunScoringPrerequisites extends AbstractQuery
{
    use Identity;
}
