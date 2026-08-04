<?php

namespace Dvsa\Olcs\Transfer\Query\DataRetention;

use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/data-retention/processed-list")
 */
final class GetProcessedList extends AbstractQuery
{
    use FieldType\Traits\StartDate;
    use FieldType\Traits\EndDate;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $dataRetentionRuleId;

    /**
     * Get Data retention rule ID
     *
     * @return int
     */
    public function getDataRetentionRuleId()
    {
        return (int)$this->dataRetentionRuleId;
    }
}
