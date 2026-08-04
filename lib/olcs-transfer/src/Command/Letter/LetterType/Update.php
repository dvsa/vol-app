<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\MasterTemplateOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterTestDataOptional;

/**
 * @Transfer\RouteName("backend/letter/letter-type/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use MasterTemplateOptional;
    use CategoryOptional;
    use SubCategoryOptional;
    use LetterTestDataOptional;


    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $name;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $description;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isActive;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $sections;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $issues;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $todos;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $appendices;

    /**
     * @var array
     * @Transfer\Optional
     */
    protected $choices;

    /**
     * @var array
     * @Transfer\Optional
     */
    protected $sectionsRequired;

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
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @return array
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @return array
     */
    public function getTodos()
    {
        return $this->todos;
    }

    /**
     * @return array
     */
    public function getAppendices()
    {
        return $this->appendices;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @return array
     */
    public function getSectionsRequired()
    {
        return $this->sectionsRequired;
    }

    /**
     * Filter out null values so optional ArrayInput properties
     * are not passed to the InputFilter (where null fails is_array check).
     */
    #[\Override]
    public function getArrayCopy()
    {
        return array_filter(parent::getArrayCopy(), fn($v) => $v !== null);
    }
}
