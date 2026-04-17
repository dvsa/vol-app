<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegVarOrCanc3 as BookmarkClass;

/**
 * BrRegVarOrCanc3 test
 */
class BrRegVarOrCanc3Test extends AbstractBrRegVarOrCanc
{
    protected const NEW_TEXT = 'register';
    protected const VARY_TEXT = 'vary';
    protected const CANCEL_TEXT = 'cancel';
    protected $bookmarkClass = BookmarkClass::class;
}
