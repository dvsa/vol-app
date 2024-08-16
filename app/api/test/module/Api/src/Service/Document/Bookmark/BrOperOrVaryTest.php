<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrOperOrVary as BookmarkClass;

/**
 * BrOperOrVary test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrOperOrVaryTest extends AbstractBrRegOrVary
{
    protected $renderReg = 'operate this service';
    protected $renderVary = 'vary this registration';
    protected $bookmarkClass = BookmarkClass::class;
}
