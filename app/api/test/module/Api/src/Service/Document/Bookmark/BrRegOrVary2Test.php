<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegOrVary2 as BookmarkClass;

/**
 * BrRegOrVary2 test
 */
class BrRegOrVary2Test extends AbstractBrRegOrVary
{
    protected $renderReg = 'new service';
    protected $renderVary = 'variation';
    protected $bookmarkClass = BookmarkClass::class;
}
