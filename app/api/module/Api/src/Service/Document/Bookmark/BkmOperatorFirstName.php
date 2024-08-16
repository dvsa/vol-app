<?php

/**
 * BkmOperatorFirstName
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark;

/**
 * BkmOperatorFirstName
 */
class BkmOperatorFirstName extends StaticBookmark
{
    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        return 'Sir or Madam';
    }
}
