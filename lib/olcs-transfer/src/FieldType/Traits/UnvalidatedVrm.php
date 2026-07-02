<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * UnvalidatedVrm Trait
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait UnvalidatedVrm
{
    /**
     * @var mixed
     * @Transfer\Optional
     */
    protected $unvalidatedVrm = null;

    /**
     * @return mixed
     */
    public function getUnvalidatedVrm()
    {
        return $this->unvalidatedVrm;
    }
}
