<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterTodo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

// phpcs:disable Generic.Commenting.Todo.TaskFound
/**
 * @Transfer\RouteName("backend/letter/letter-todo/single")
 * @Transfer\Method("PUT")
 */
// phpcs:enable Generic.Commenting.Todo.TaskFound
final class Update extends AbstractCommand
{
    use Identity;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    protected $todoKey;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $description;

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
    public function getTodoKey()
    {
        return $this->todoKey;
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return $this->description;
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
