<?php

namespace Dvsa\Olcs\Transfer\Command\DataRetention;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/data-retention/delay-items")
 * @Transfer\Method("POST")
 */
final class DelayItems extends AbstractCommand
{
    use Ids;

    /**
     * @Transfer\Validator("Date",options={"format":"Y-m-d"})
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\DateInFuture")
     * @Transfer\Optional
     */
    public $nextReviewDate;

    /**
     * @return mixed
     */
    public function getNextReviewDate()
    {
        return $this->nextReviewDate;
    }
}
