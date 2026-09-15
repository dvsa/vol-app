<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/letter/issue-type")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":10})
     */
    protected $code;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    protected $name;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    protected $description;

    /**
     * @var int
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
