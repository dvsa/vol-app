<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterSection;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\GoodsOrPsvOptional;

/**
 * @Transfer\RouteName("backend/letter/letter-section")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use GoodsOrPsvOptional;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    protected $sectionKey;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $name;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"letter_section_type_header", "letter_section_type_body", "letter_section_type_footer"}})
     */
    protected $sectionType;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $defaultContent;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isNi = false;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $requiresInput = false;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $minLength;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $maxLength;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $helpText;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d H:i:s"})
     */
    protected $publishFrom;

    /**
     * @return string
     */
    public function getSectionKey()
    {
        return $this->sectionKey;
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
    public function getSectionType()
    {
        return $this->sectionType;
    }

    /**
     * @return array
     */
    public function getDefaultContent()
    {
        return $this->defaultContent;
    }

    /**
     * @return bool
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * @return bool
     */
    public function getRequiresInput()
    {
        return $this->requiresInput;
    }

    /**
     * @return int
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * @return string
     */
    public function getPublishFrom()
    {
        return $this->publishFrom;
    }
}
