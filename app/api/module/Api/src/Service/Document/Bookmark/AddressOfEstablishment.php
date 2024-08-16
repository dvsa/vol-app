<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Address of Establishment bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AddressOfEstablishment extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'establishmentCd' => [
                'address'
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (isset($this->data['establishmentCd']['address'])) {
            return Formatter\Address::format($this->data['establishmentCd']['address']);
        }
        return '';
    }
}
