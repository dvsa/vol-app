<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Application checked
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait ApplicationCheckedOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $checked;

    /**
     * @return bool
     */
    public function getChecked()
    {
        return $this->checked;
    }
}
