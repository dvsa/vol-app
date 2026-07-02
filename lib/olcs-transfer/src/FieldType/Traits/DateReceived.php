<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait DateReceived
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait DateReceived
{
    /**
     * @Transfer\Optional
     * @var \DateTime
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d"})
     */
    protected $dateReceived;

    /**
     * @return \DateTime
     */
    public function getDateReceived()
    {
        return $this->dateReceived;
    }
}
