<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Venue Bundle
 */
class VenueBundle extends AbstractQuery
{
    use Identity;

    protected $bundle = [];

    /**
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
