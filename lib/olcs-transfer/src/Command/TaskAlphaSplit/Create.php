<?php

namespace Dvsa\Olcs\Transfer\Command\TaskAlphaSplit;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/task-alpha-split")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use \Dvsa\Olcs\Transfer\FieldType\Traits\User;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $taskAllocationRule;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":50})
     */
    protected $letters;

    public function getTaskAllocationRule()
    {
        return $this->taskAllocationRule;
    }

    public function getLetters()
    {
        return $this->letters;
    }
}
