<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Traffic Area Name bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaName extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (empty($data['licence'])) {
            return null;
        }

        return Qry::create(['id' => $data['licence'], 'bundle' => ['trafficArea']]);
    }

    public function render()
    {
        return $this->data['trafficArea']['name'];
    }
}
