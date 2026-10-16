<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait MultipleNoOfPermits
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait MultipleNoOfPermits
{
    #[Transfer\ArrayInput]
    protected $permitsRequired;

    /**
     * @return array
     */
    public function getPermitsRequired()
    {
        return $this->permitsRequired;
    }
}
