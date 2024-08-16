<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\CommunityLicBundle as Qry;

/**
 * Original Copy bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OriginalCopy extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['communityLic']]);
    }

    public function render()
    {
        if ($this->data['issueNo'] === 0) {
            return 'LICENCE';
        }
        return 'CERTIFIED TRUE COPY';
    }
}
