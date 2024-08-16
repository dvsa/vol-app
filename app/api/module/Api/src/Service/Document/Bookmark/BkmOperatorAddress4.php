<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark;

/**
 * BkmOperatorAddress4
 */
class BkmOperatorAddress4 extends StaticBookmark
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
