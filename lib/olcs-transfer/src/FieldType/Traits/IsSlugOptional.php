<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Is slug
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IsSlugOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $isSlug;

    /**
     * @return bool
     */
    public function getIsSlug()
    {
        return $this->isSlug;
    }
}
