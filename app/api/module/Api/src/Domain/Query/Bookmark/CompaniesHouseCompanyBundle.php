<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

class CompaniesHouseCompanyBundle extends AbstractQuery
{
    use Identity;

    protected $bundle = [];

    public function getBundle(): array
    {
        return $this->bundle;
    }
}
