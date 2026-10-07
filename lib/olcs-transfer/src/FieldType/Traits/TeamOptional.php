<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
trait TeamOptional
{
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    protected $team;

    public function getTeam()
    {
        return $this->team;
    }
}
