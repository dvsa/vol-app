<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Application Bundle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBundle extends AbstractQuery
{
    use Traits\IdentityOptional;
    use Traits\CasesOptional;

    protected $bundle = [];

    /**
     * Get the bundle
     *
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
