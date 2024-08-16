<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * BusFeeType Bundle
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeeTypeBundle extends AbstractQuery
{
    use Identity;

    /**
     * @var array
     */
    protected $bundle = [];

    /**
     * get bundle
     *
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
