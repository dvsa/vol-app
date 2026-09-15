<?php

/**
 * Create a Presiding TC
 */

namespace Dvsa\Olcs\Transfer\Command\Cases\PresidingTc;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/presiding-tc")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use User;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":70})
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
