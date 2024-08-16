<?php

/**
 * IrfoSignature
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark;

/**
 * IrfoSignature
 */
class IrfoSignature extends StaticBookmark
{
    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        return 'International Road Freight Office';
    }
}
