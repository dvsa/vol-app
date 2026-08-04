<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterChoice;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/letter/letter-choice")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    protected $choiceKey;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $label;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $groupLabel;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"checkbox", "radio"}})
     */
    protected $inputType;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $displayOrder;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isActive = true;

    /**
     * @return string
     */
    public function getChoiceKey()
    {
        return $this->choiceKey;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->groupLabel;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
