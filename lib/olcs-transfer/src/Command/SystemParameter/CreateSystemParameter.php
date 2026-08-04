<?php

/**
 * Create System Parameter
 */

namespace Dvsa\Olcs\Transfer\Command\SystemParameter;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/system-parameter")
 * @Transfer\Method("POST")
 */
final class CreateSystemParameter extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":1024})
     */
    protected $paramValue;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":255})
     * @Transfer\Optional
     */
    protected $description;

    public function getId()
    {
        return $this->id;
    }

    public function getParamValue()
    {
        return $this->paramValue;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
