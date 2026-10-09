<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait Category
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
trait Category
{
    /**
     * @var int
     */
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $category;

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }
}
