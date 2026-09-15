<?php

/**
 * Get continuation details list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\ContinuationDetail;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/continuation-detail/list")
 */
final class GetList extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $continuationId;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\LicenceStatus")
     * @Transfer\Optional
     */
    protected $licenceStatus = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":18})
     * @Transfer\Optional
     */
    protected $licenceNo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "post",
     *              "email",
     *              "all"
     *          }
     *      }
     * )
     * @Transfer\Optional
     */
    protected $method;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "con_det_sts_prepared",
     *              "con_det_sts_printing",
     *              "con_det_sts_printed",
     *              "con_det_sts_unacceptable",
     *              "con_det_sts_acceptable",
     *              "con_det_sts_complete",
     *              "con_det_sts_error"
     *          }
     *      }
     * )
     * @Transfer\Optional
     */
    protected $status;

    /**
     * @return mixed
     */
    public function getContinuationId()
    {
        return $this->continuationId;
    }

    /**
     * @return mixed
     */
    public function getLicenceStatus()
    {
        return $this->licenceStatus;
    }

    /**
     * @return mixed
     */
    public function getLicenceNo()
    {
        return $this->licenceNo;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
}
