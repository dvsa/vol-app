<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait DateReceived
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait DateReceived
{
    /**
     * @var \DateTime
     */
    #[Transfer\Optional]
    #[Transfer\Validator('Laminas\Validator\Date', options: ['format' => 'Y-m-d'])]
    protected $dateReceived;

    /**
     * @return \DateTime
     */
    public function getDateReceived()
    {
        return $this->dateReceived;
    }
}
