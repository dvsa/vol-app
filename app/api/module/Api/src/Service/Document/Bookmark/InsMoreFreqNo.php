<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * InsMoreFreqNo bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsMoreFreqNo extends DynamicBookmark
{
    #[\Override]
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence']]);
    }

    #[\Override]
    public function render()
    {
        if (!$this->data['safetyInsVaries']) {
            return 'X';
        }
        return '';
    }
}
