<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait MultipleNoOfPermits Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait MultipleNoOfPermitsOptional
{
    /**
     * @Transfer\Optional()
     * @Transfer\ArrayInput
     */
    protected $permitsRequired = [];

    /**
     * @return array
     */
    public function getPermitsRequired()
    {
        return $this->permitsRequired;
    }
}
