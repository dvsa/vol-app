<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
/**
 * Suppress From OP
 */
trait SuppressFromOp
{
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Dvsa\Olcs\Transfer\Validators\YesNo')]
    #[Transfer\Optional]
    protected $suppressFromOp;

    /**
     * @return string
     */
    public function getSuppressFromOp()
    {
        return $this->suppressFromOp;
    }
}
