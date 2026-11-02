<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Application step slug
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait ApplicationStepSlug
{
    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $slug;

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
