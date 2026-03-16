<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterIssue;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\GoodsOrPsvOptional;

/**
 * @Transfer\RouteName("backend/letter/letter-issue")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use Category;
    use SubCategoryOptional;
    use GoodsOrPsvOptional;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    protected $issueKey;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $heading;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":200})
     */
    protected $modalLabel;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $defaultBodyContent;

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
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $letterIssueTypeId;

    /**
     * @return string
     */
    public function getIssueKey()
    {
        return $this->issueKey;
    }

    /**
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * @return string
     */
    public function getModalLabel()
    {
        return $this->modalLabel;
    }

    /**
     * @return array
     */
    public function getDefaultBodyContent()
    {
        return $this->defaultBodyContent;
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

    /**
     * @return int
     */
    public function getLetterIssueTypeId()
    {
        return $this->letterIssueTypeId;
    }
}
