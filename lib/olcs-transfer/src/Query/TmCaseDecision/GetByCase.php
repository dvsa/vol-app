<?php

namespace Dvsa\Olcs\Transfer\Query\TmCaseDecision;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * Class GetByCase
 * @Transfer\RouteName("backend/tm-case-decision")
 */
class GetByCase extends AbstractQuery implements FieldType\CasesInterface
{
    use FieldType\Traits\Cases;
}
