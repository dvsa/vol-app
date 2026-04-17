<?php

/**
 * BkmOurRef
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * BkmOurRef
 */
class BkmOurRef extends BkmAuthNo
{
    /**
     * Renders the bookmark
     *
     * @return string
     */
    #[\Override]
    public function render()
    {
        return 'PT2/21/' . parent::render();
    }
}
