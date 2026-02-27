<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegOrVary as BookmarkClass;

/**
 * BrRegOrVary test
 */
class BrRegOrVaryTest extends AbstractBrRegOrVary
{
    protected const RENDER_REG = 'register';
    protected const RENDER_VARY = 'vary';
    protected $bookmarkClass = BookmarkClass::class;
}
