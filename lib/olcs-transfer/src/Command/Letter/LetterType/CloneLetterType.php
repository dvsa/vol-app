<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/letter/letter-type/clone")
 * @Transfer\Method("POST")
 */
final class CloneLetterType extends AbstractCommand
{
    use Identity;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":50})
     */
    protected $newCode;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $newName;

    /**
     * @return string
     */
    public function getNewCode()
    {
        return $this->newCode;
    }

    /**
     * @return string
     */
    public function getNewName()
    {
        return $this->newName;
    }
}
