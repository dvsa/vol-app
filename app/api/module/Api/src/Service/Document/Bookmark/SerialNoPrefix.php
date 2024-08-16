<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\CommunityLicBundle as Qry;

/**
 * Serial number prefix bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class SerialNoPrefix extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['communityLic']]);
    }

    public function render()
    {
        return $this->data['serialNoPrefix'];
    }
}
