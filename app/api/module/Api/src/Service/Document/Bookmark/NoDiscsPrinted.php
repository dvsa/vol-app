<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * Number of Discs bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class NoDiscsPrinted extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        /**
         * We don't want to query; we trust that our value will be
         * supplied by the consumer somehow.
         * It's not a queryable (?) value
         */
        return null;
    }

    public function render()
    {
        return $this->data['count'];
    }
}
