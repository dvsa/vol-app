<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * TransportManager Bundle
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
class TransportManagerLicenceBundle extends AbstractQuery
{
    use Identity;

    protected $bundle = [];

    public function getBundle(): array
    {
        return $this->bundle;
    }
}
