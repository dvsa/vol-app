<?php

namespace Dvsa\Olcs\Transfer\Query\Bus\Ebsr;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class EbsrSubmission
 *
 * @Transfer\RouteName("backend/ebsr-submission/single")
 */
class EbsrSubmission extends AbstractQuery
{
    use Identity;
}
