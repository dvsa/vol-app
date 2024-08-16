<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegVarOrCanc3 as BookmarkClass;

/**
 * BrRegVarOrCanc3 test
 */
class BrRegVarOrCanc3Test extends AbstractBrRegVarOrCanc
{
    protected $new = 'register';
    protected $vary = 'vary';
    protected $cancel = 'cancel';
    protected $bookmarkClass = BookmarkClass::class;
}
