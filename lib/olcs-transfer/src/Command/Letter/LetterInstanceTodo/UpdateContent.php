<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Override a to-do's wording for one letter.
 *
 * The field is editedDescription, not editedContent like the three sibling commands: a to-do's
 * wording is called `description` everywhere in its own code path, so the override is named after
 * what it overrides.
 *
 * @Transfer\RouteName("backend/letter/letter-instance-todo/single")
 * @Transfer\Method("PUT")
 */
final class UpdateContent extends AbstractCommand
{
    use Identity;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":2})
     * @Transfer\Escape(false)
     */
    protected $editedDescription;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @return string
     */
    public function getEditedDescription()
    {
        return $this->editedDescription;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
