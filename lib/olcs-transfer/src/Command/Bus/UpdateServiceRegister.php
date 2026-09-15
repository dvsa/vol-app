<?php

/**
 * Update Service Register
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/service-register")
 * @Transfer\Method("PUT")
 */
final class UpdateServiceRegister extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $timetableAcceptable;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $mapSupplied;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":1000})
     * @Transfer\Optional
     */
    protected $routeDescription;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $trcConditionChecked;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    protected $trcNotes;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $copiedToLaPte;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $laShortNote;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $opNotifiedLaPte;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $applicationSigned;

    /**
     * @return string
     */
    public function getTimetableAcceptable()
    {
        return $this->timetableAcceptable;
    }

    /**
     * @return string
     */
    public function getMapSupplied()
    {
        return $this->mapSupplied;
    }

    /**
     * @return string
     */
    public function getRouteDescription()
    {
        return $this->routeDescription;
    }

    /**
     * @return string
     */
    public function getTrcConditionChecked()
    {
        return $this->trcConditionChecked;
    }

    /**
     * @return string
     */
    public function getTrcNotes()
    {
        return $this->trcNotes;
    }

    /**
     * @return string
     */
    public function getCopiedToLaPte()
    {
        return $this->copiedToLaPte;
    }

    /**
     * @return string
     */
    public function getLaShortNote()
    {
        return $this->laShortNote;
    }

    /**
     * @return string
     */
    public function getOpNotifiedLaPte()
    {
        return $this->opNotifiedLaPte;
    }

    /**
     * @return string
     */
    public function getApplicationSigned()
    {
        return $this->applicationSigned;
    }
}
