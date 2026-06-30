<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Is Pre Grant
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IsPreGrant
{
    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $isPreGrant = false;

    /**
     * @return bool
     */
    public function getIsPreGrant()
    {
        return $this->isPreGrant;
    }
}
