<?php

/**
 * Create Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\PreviousConviction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/previous-conviction")
 * @Transfer\Method("POST")
 */
final class CreatePreviousConviction extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $application;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $transportManager;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Title")
     * @Transfer\Optional
     */
    protected $title;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     * @Transfer\Optional
     */
    protected $forename;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     * @Transfer\Optional
     */
    protected $familyName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $convictionDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":70})
     */
    protected $courtFpn;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":1024})
     */
    protected $categoryText;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":4000})
     */
    protected $notes;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":255})
     */
    protected $penalty;

    /**
     * Get application
     *
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get transport manager
     *
     * @return int
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get forename
     *
     * @return string
     */
    public function getForename()
    {
        return $this->forename;
    }

    /**
     * Get family name
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Get conviction date
     *
     * @return string
     */
    public function getConvictionDate()
    {
        return $this->convictionDate;
    }

    /**
     * Get court fpn
     *
     * @return string
     */
    public function getCourtFpn()
    {
        return $this->courtFpn;
    }

    /**
     * Get category text
     *
     * @return string
     */
    public function getCategoryText()
    {
        return $this->categoryText;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Get penalty
     *
     * @return string
     */
    public function getPenalty()
    {
        return $this->penalty;
    }
}
