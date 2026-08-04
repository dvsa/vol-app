<?php

/**
 * Delete System Parameter
 */

namespace Dvsa\Olcs\Transfer\Command\SystemParameter;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/system-parameter/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteSystemParameter extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
