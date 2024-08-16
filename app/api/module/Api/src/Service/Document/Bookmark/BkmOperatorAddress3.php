<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark;

/**
 * BkmOperatorAddress3
 */
class BkmOperatorAddress3 extends StaticBookmark
{
    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        // full address rendered by BkmOperatorAddress1
        return '';
    }
}
