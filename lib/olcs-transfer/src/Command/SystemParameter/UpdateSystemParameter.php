<?php

/**
 * Update System Parameter
 */

namespace Dvsa\Olcs\Transfer\Command\SystemParameter;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/system-parameter/single")
 * @Transfer\Method("PUT")
 */
final class UpdateSystemParameter extends AbstractCommand
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

    /**
     * @return int
     */
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
