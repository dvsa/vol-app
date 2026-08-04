<?php

/**
 * Create Inspection Request
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\InspectionRequest;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/inspection-request/create")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $operatingCentre;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"application", "licence"}})
     */
    protected $type;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={
     *          "haystack": {
     *              "insp_rep_t_bus", "insp_rep_t_maint", "insp_rep_t_TE"
     *          }
     *      }
     * )
     */
    protected $reportType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "insp_req_t_coe", "insp_req_t_comp", "insp_req_t_fol", "insp_req_t_new_op", "insp_req_t_review",
     *              "insp_req_t_tc", "insp_req_t_var"
     *          }
     *      }
     * )
     */
    protected $requestType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "insp_res_t_new", "insp_res_t_new_sat", "insp_res_t_new_unsat"
     *          }
     *      }
     * )
     */
    protected $resultType;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $dueDate;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $requestDate;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $returnDate;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $fromDate;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $toDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $inspectorName;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $vehiclesExaminedNo;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $trailersExaminedNo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $requestorNotes;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $inspectorNotes;

    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    public function getLicence()
    {
        return $this->licence;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getReportType()
    {
        return $this->reportType;
    }

    public function getRequestType()
    {
        return $this->requestType;
    }

    public function getResultType()
    {
        return $this->resultType;
    }

    public function getDueDate()
    {
        return $this->dueDate;
    }

    public function getRequestDate()
    {
        return $this->requestDate;
    }

    public function getReturnDate()
    {
        return $this->returnDate;
    }

    public function getFromDate()
    {
        return $this->fromDate;
    }

    public function getToDate()
    {
        return $this->toDate;
    }

    public function getInspectorName()
    {
        return $this->inspectorName;
    }

    public function getVehiclesExaminedNo()
    {
        return $this->vehiclesExaminedNo;
    }

    public function getTrailersExaminedNo()
    {
        return $this->trailersExaminedNo;
    }

    public function getRequestorNotes()
    {
        return $this->requestorNotes;
    }

    public function getInspectorNotes()
    {
        return $this->inspectorNotes;
    }
}
