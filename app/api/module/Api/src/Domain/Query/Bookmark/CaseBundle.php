<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Case Bundle
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CaseBundle extends AbstractQuery
{
    use Identity;

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
