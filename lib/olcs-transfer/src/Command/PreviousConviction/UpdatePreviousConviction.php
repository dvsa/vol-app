<?php

/**
 * Update Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\PreviousConviction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/previous-conviction/single")
 * @Transfer\Method("PUT")
 */
final class UpdatePreviousConviction extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $transportManager;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={"haystack": {"title_dr","title_miss","title_mr","title_mrs","title_ms"}}
     * )
     * @Transfer\Optional
     */
    protected $title;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $forename;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $familyName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $convictionDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $courtFpn;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $categoryText;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $notes;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $penalty;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
