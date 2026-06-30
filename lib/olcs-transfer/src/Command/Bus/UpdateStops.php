<?php

/**
 * Bus stops
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/stops")
 * @Transfer\Method("PUT")
 */
final class UpdateStops extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $useAllStops;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $hasManoeuvre;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $manoeuvreDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $needNewStop;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $newStopDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $hasNotFixedStop;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $notFixedStopDetail;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"bs_no", "bs_yes", "bs_in_part"}})
     */
    protected $subsidised;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $subsidyDetail;

    /**
     * @return string
     */
    public function getUseAllStops()
    {
        return $this->useAllStops;
    }

    /**
     * @return string
     */
    public function getHasManoeuvre()
    {
        return $this->hasManoeuvre;
    }

    /**
     * @return string
     */
    public function getManoeuvreDetail()
    {
        return $this->manoeuvreDetail;
    }

    /**
     * @return string
     */
    public function getNeedNewStop()
    {
        return $this->needNewStop;
    }

    /**
     * @return string
     */
    public function getNewStopDetail()
    {
        return $this->newStopDetail;
    }

    /**
     * @return string
     */
    public function getHasNotFixedStop()
    {
        return $this->hasNotFixedStop;
    }

    /**
     * @return string
     */
    public function getNotFixedStopDetail()
    {
        return $this->notFixedStopDetail;
    }

    /**
     * @return string
     */
    public function getSubsidised()
    {
        return $this->subsidised;
    }

    /**
     * @return string
     */
    public function getSubsidyDetail()
    {
        return $this->subsidyDetail;
    }
}
