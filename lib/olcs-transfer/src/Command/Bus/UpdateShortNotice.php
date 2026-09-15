<?php

/**
 * Update bus short notice
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/short-notice")
 * @Transfer\Method("PUT")
 */
final class UpdateShortNotice extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $bankHolidayChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $unforseenChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $unforseenDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $timetableChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $timetableDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $replacementChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $replacementDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $notAvailableChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $notAvailableDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $specialOccasionChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $specialOccasionDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $connectionChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $connectionDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $holidayChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $holidayDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $trcChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $trcDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $policeChange;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $policeDetail;

    /**
     * @return string
     */
    public function getBankHolidayChange()
    {
        return $this->bankHolidayChange;
    }

    /**
     * @return string
     */
    public function getUnforseenChange()
    {
        return $this->unforseenChange;
    }

    /**
     * @return string
     */
    public function getUnforseenDetail()
    {
        return $this->unforseenDetail;
    }

    /**
     * @return string
     */
    public function getTimetableChange()
    {
        return $this->timetableChange;
    }

    /**
     * @return string
     */
    public function getTimetableDetail()
    {
        return $this->timetableDetail;
    }

    /**
     * @return string
     */
    public function getReplacementChange()
    {
        return $this->replacementChange;
    }

    /**
     * @return string
     */
    public function getReplacementDetail()
    {
        return $this->replacementDetail;
    }

    /**
     * @return string
     */
    public function getNotAvailableChange()
    {
        return $this->notAvailableChange;
    }

    /**
     * @return string
     */
    public function getNotAvailableDetail()
    {
        return $this->notAvailableDetail;
    }

    /**
     * @return string
     */
    public function getSpecialOccasionChange()
    {
        return $this->specialOccasionChange;
    }

    /**
     * @return string
     */
    public function getSpecialOccasionDetail()
    {
        return $this->specialOccasionDetail;
    }

    /**
     * @return string
     */
    public function getConnectionChange()
    {
        return $this->connectionChange;
    }

    /**
     * @return string
     */
    public function getConnectionDetail()
    {
        return $this->connectionDetail;
    }

    /**
     * @return string
     */
    public function getHolidayChange()
    {
        return $this->holidayChange;
    }

    /**
     * @return string
     */
    public function getHolidayDetail()
    {
        return $this->holidayDetail;
    }

    /**
     * @return string
     */
    public function getTrcChange()
    {
        return $this->trcChange;
    }

    /**
     * @return string
     */
    public function getTrcDetail()
    {
        return $this->trcDetail;
    }

    /**
     * @return string
     */
    public function getPoliceChange()
    {
        return $this->policeChange;
    }

    /**
     * @return string
     */
    public function getPoliceDetail()
    {
        return $this->policeDetail;
    }
}
