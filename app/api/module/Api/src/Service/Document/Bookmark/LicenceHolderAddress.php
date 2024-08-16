<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence holder address bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceHolderAddress extends DynamicBookmark
{
    protected $params = ['licence'];

    public function getQuery(array $data)
    {
        $bundle = [
            'correspondenceCd' => [
                'address'
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (isset($this->data['correspondenceCd']['address'])) {
            return Formatter\Address::format($this->data['correspondenceCd']['address']);
        }
        return '';
    }
}
