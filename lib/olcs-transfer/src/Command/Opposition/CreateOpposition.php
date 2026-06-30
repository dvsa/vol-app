<?php

namespace Dvsa\Olcs\Transfer\Command\Opposition;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/opposition")
 * @Transfer\Method("POST")
 */
class CreateOpposition extends AbstractCommand
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"otf_eob", "otf_obj", "otf_rep"}})
     */
    protected $oppositionType;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $raisedDate = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={"haystack": {"obj_t_local_auth", "obj_t_other", "obj_t_police", "obj_t_rta",
     *                  "obj_t_trade_union"
     *      }}
     * )
     */
    protected $opposerType = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"opp_v_yes","opp_v_no","opp_v_nd"}})
     */
    protected $isValid;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":4000})
     */
    protected $validNotes;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isCopied;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isWillingToAttendPi;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isInTime;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isWithdrawn;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"opp_ack","opp_cu_acc","opp_cu_prop","opp_cu_ref","opp_pro_rec"}})
     */
    protected $status = null;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $operatingCentres = [];

    /**
     * @Transfer\ArrayInput
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={"haystack": {"ogf_both","ogf_env","ogf_fin_stan","ogf_fitness","ogf_fumes","ogf_noise","ogf_o",
     *      "ogf_o_ccap","ogf_parking","ogf_pollution","ogf_prof_com","ogf_repute","ogf_safety","ogf_size",
     *      "ogf_unsochrs","ogf_vib","ogf_vis"}}
     * )
     */
    protected $grounds = [];

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":4000})
     */
    protected $notes = null;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\ContactDetails")
     * @Transfer\Optional
     */
    protected $opposerContactDetails;

    /**
     * @return mixed
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * @return mixed
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return mixed
     */
    public function getGrounds()
    {
        return $this->grounds;
    }

    /**
     * @return mixed
     */
    public function getIsCopied()
    {
        return $this->isCopied;
    }

    /**
     * @return mixed
     */
    public function getIsInTime()
    {
        return $this->isInTime;
    }

    /**
     * @return mixed
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * @return mixed
     */
    public function getIsWillingToAttendPi()
    {
        return $this->isWillingToAttendPi;
    }

    /**
     * @return mixed
     */
    public function getIsWithdrawn()
    {
        return $this->isWithdrawn;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return mixed
     */
    public function getOpposerContactDetails()
    {
        return $this->opposerContactDetails;
    }

    /**
     * @return mixed
     */
    public function getOpposerType()
    {
        return $this->opposerType;
    }

    /**
     * @return mixed
     */
    public function getOppositionType()
    {
        return $this->oppositionType;
    }

    /**
     * @return mixed
     */
    public function getRaisedDate()
    {
        return $this->raisedDate;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getValidNotes()
    {
        return $this->validNotes;
    }
}
