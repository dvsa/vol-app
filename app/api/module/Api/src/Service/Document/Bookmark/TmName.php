<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerBundle as Qry;

/**
 * Transport manager name bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmName extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'homeCd' => [
                'person'
            ]
        ];
        return Qry::create(['id' => $data['transportManager'], 'bundle' => $bundle]);
    }

    public function render()
    {
        return $this->data['homeCd']['person']['forename'] . ' ' . $this->data['homeCd']['person']['familyName'];
    }
}
