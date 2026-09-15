<?php

namespace Dvsa\Olcs\Transfer\Query\Complaint;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class ComplaintList
 * @Transfer\RouteName("backend/complaint")
 */
class ComplaintList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Validator("Laminas\Validator\Identical", options={"token": true})
     */
    protected $isCompliance = true;

    /**
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return int
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return bool
     */
    public function getIsCompliance()
    {
        return $this->isCompliance;
    }
}
