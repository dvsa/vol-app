<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Add an empty caseworker-written to-do to an Other issue on a letter (VOL-7735)
 */
#[Transfer\RouteName('backend/letter/letter-instance-todo/create-custom')]
#[Transfer\Method('POST')]
final class CreateCustom extends AbstractCommand
{
    /**
     * The letter's Other issue (letter_instance_issue id)
     *
     * @var int
     */
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $letterInstanceIssue;

    /**
     * @return int
     */
    public function getLetterInstanceIssue()
    {
        return $this->letterInstanceIssue;
    }
}
