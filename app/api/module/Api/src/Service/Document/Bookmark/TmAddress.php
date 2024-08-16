<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerBundle as Qry;

/**
 * Transport manager address bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmAddress extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle  = [
            'homeCd' => [
                'address'
            ]
        ];

        return Qry::create(['id' => $data['transportManager'], 'bundle' => $bundle]);
    }

    public function render()
    {
        return Formatter\Address::format($this->data['homeCd']['address']);
    }
}
