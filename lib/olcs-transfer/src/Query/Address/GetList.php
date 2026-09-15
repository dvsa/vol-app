<?php

/**
 * Get a list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Address;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;

/**
 * @Transfer\RouteName("backend/address/list")
 */
final class GetList extends AbstractQuery implements CacheableMediumTermQueryInterface
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":8})
     */
    protected $postcode;

    /**
     * Get a postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
}
