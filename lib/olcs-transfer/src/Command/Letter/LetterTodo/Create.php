<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterTodo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

// phpcs:disable Generic.Commenting.Todo.TaskFound
// phpcs:enable Generic.Commenting.Todo.TaskFound
#[Transfer\RouteName('backend/letter/letter-todo')]
#[Transfer\Method('POST')]
final class Create extends AbstractCommand
{
    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 100])]
    protected $todoKey;

    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 255])]
    protected $name;

    /**
     * @var array
     */
    #[Transfer\Optional]
    #[Transfer\Escape(false)]
    protected $description;

    /**
     * @var string
     */
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $helpText;

    /**
     * @var bool
     */
    #[Transfer\Optional]
    protected $requiresInput = false;

    /**
     * @var string
     */
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\DateTimeFormatter')]
    #[Transfer\Validator('Laminas\Validator\Date', options: ['format' => 'Y-m-d H:i:s'])]
    protected $publishFrom;

    /**
     * @return string
     */
    public function getTodoKey()
    {
        return $this->todoKey;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return bool
     */
    public function getRequiresInput()
    {
        return $this->requiresInput;
    }

    /**
     * @return string
     */
    public function getPublishFrom()
    {
        return $this->publishFrom;
    }
}
