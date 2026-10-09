<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * IrhpPermitRangePrefix
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangePrefix
{
    /**
     *
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 255])]
    #[Transfer\Optional]
    protected $prefix;

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
