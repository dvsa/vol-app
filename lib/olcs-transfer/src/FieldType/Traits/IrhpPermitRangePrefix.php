<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IrhpPermitRangePrefix
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangePrefix
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":255})
     * @Transfer\Optional
     *
     * @var string
     */
    protected $prefix;

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
