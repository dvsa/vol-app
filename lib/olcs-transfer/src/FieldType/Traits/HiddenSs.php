<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * HiddenSs (Hidden from Self Service)
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait HiddenSs
{
    #[Transfer\Filter('Laminas\Filter\Boolean')]
    #[Transfer\Optional]
    protected $hiddenSs;

    /**
     * @return bool
     */
    public function getHiddenSs()
    {
        return $this->hiddenSs;
    }
}
