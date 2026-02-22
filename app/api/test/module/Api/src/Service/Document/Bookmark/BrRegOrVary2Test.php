<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegOrVary2 as BookmarkClass;

/**
 * BrRegOrVary2 test
 */
class BrRegOrVary2Test extends AbstractBrRegOrVary
{
    protected const RENDER_REG = 'new service';
    protected const RENDER_VARY = 'variation';
    protected $bookmarkClass = BookmarkClass::class;
}
