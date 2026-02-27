<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrOperOrVary as BookmarkClass;

/**
 * BrOperOrVary test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrOperOrVaryTest extends AbstractBrRegOrVary
{
    protected const RENDER_REG = 'operate this service';
    protected const RENDER_VARY = 'vary this registration';
    protected $bookmarkClass = BookmarkClass::class;
}
