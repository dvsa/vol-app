<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait Irhp Permit Range Restricted Countries
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeRestrictedCountries
{
    /**
     * @var array
     */
    #[Transfer\Optional]
    protected $countrys = [];

    /**
     * @return array
     */
    public function getRestrictedCountries()
    {
        return $this->countrys;
    }
}
