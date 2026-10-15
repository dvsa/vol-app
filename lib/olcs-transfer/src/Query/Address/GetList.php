<?php

/**
 * Get a list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Address;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;

#[Transfer\RouteName("backend/address/list")]
final class GetList extends AbstractQuery implements CacheableMediumTermQueryInterface
{
    #[Transfer\Optional]
    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    #[Transfer\Validator("Laminas\Validator\StringLength", options: ["max" => 8])]
    protected string $postcode = '';

    /**
     * Get a postcode
     *
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }
}
