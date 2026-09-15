<?php

namespace Dvsa\Olcs\Transfer\Query\Irfo;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class IrfoPsvAuthContinuationList
 * @Transfer\RouteName("backend/irfo/psv-auth/continuation")
 */
class IrfoPsvAuthContinuationList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 1, "max": 12})
     */
    public $month;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    public $year;

    /**
     * Get a month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Get a year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }
}
