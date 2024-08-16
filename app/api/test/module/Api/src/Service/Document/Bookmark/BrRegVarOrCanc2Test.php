<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegVarOrCanc2 as BookmarkClass;

/**
 * BrRegVarOrCanc2 test
 */
class BrRegVarOrCanc2Test extends AbstractBrRegVarOrCanc
{
    protected $new = 'REGISTER';
    protected $vary = 'VARY';
    protected $cancel = 'CANCEL';
    protected $bookmarkClass = BookmarkClass::class;
}
