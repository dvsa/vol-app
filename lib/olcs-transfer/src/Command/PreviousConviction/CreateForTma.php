<?php

/**
 * Create Previous Conviction for a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\PreviousConviction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/previous-conviction/tma")
 * @Transfer\Method("POST")
 */
final class CreateForTma extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $tmaId;

    /**
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d"})
     */
    protected $convictionDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":1024})
     */
    protected $categoryText;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min": 0, "max": 4000})
     */
    protected $notes;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min": 0, "max": 70})
     */
    protected $courtFpn;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":255})
     */
    protected $penalty;

    /**
     * Get Transport Manager Application Id
     */
    public function getTmaId()
    {
        return $this->tmaId;
    }

    /**
     *
     * @return string YYYY-MM-DD
     */
    public function getConvictionDate()
    {
        return $this->convictionDate;
    }

    /**
     *
     * @return string
     */
    public function getCategoryText()
    {
        return $this->categoryText;
    }

    /**
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     *
     * @return string
     */
    public function getCourtFpn()
    {
        return $this->courtFpn;
    }

    /**
     *
     * @return string
     */
    public function getPenalty()
    {
        return $this->penalty;
    }
}
