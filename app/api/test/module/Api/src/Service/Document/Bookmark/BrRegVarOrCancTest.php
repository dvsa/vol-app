<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegVarOrCanc as BookmarkClass;

/**
 * AbstractBrRegVarOrCanc test
 */
class BrRegVarOrCancTest extends AbstractBrRegVarOrCanc
{
    protected $new = 'commence';
    protected $vary = 'vary';
    protected $cancel = 'cancel';
    protected $bookmarkClass = BookmarkClass::class;
}
