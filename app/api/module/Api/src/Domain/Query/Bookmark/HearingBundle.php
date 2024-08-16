<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Cases;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Hearing Bundle
 */
class HearingBundle extends AbstractQuery
{
    use Cases;

    protected $bundle = [];

    /**
     * Get bundle
     *
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
