<?php

/**
 * Get address for uprn
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Address;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;

/**
 * @Transfer\RouteName("backend/address/details")
 */
final class GetAddress extends AbstractQuery implements CacheableMediumTermQueryInterface
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $uprn;

    /**
     * Get UPRN
     *
     * @return int
     */
    public function getUprn()
    {
        return $this->uprn;
    }
}
