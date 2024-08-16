<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * TransportManager Bundle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerBundle extends AbstractQuery
{
    use Identity;

    protected $bundle = [];

    /**
     * @return mixed
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
