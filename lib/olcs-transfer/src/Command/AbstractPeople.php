<?php

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class AbstractPeople extends AbstractCommand
{
    //  Identity is Licence id
    use Traits\Identity;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Title")
     * @Transfer\Optional
     */
    protected $title;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    protected $forename;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    protected $familyName;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     * @Transfer\Optional
     */
    protected $otherName;

    /**
     * @var string
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $birthDate;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":45})
     * @Transfer\Optional
     */
    protected $position;

    /**
     * Get Forename
     *
     * @return string
     */
    public function getForename()
    {
        return $this->forename;
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get Family Name
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Get Other Name
     *
     * @return string
     */
    public function getOtherName()
    {
        return $this->otherName;
    }

    /**
     * Get Birth Date
     *
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Get Position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
