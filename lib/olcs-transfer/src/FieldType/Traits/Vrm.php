<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Vrm Trait
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait Vrm
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("\Dvsa\Olcs\Transfer\Filter\Vrm")
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\Vrm")
     */
    protected $vrm = null;

    /**
     * @return mixed
     */
    public function getVrm()
    {
        return $this->vrm;
    }
}
