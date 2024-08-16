<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegOrVary as BookmarkClass;

/**
 * BrRegOrVary test
 */
class BrRegOrVaryTest extends AbstractBrRegOrVary
{
    protected $renderReg = 'register';
    protected $renderVary = 'vary';
    protected $bookmarkClass = BookmarkClass::class;
}
