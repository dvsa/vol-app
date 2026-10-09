<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Valid only
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait ValidOnlyOptional
{
    #[Transfer\Filter('Laminas\Filter\Boolean')]
    #[Transfer\Optional]
    protected $validOnly;

    /**
     * @return ?bool
     */
    public function getValidOnly()
    {
        return $this->validOnly;
    }
}
