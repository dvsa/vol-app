<?php

namespace Dvsa\Olcs\Transfer\Query\Bus;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class BusRegBrowseExport
 * @Transfer\RouteName("backend/bus/browse/export")
 */
class BusRegBrowseExport extends AbstractQuery
{
    use FieldType\TrafficAreas;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":36})
     * @Transfer\Optional
     */
    protected $status;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\DateNotInFuture")
     */
    protected $acceptedDate;

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get accepted date
     *
     * @return string
     */
    public function getAcceptedDate()
    {
        return $this->acceptedDate;
    }
}
