<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegVarOrCanc2 as BookmarkClass;

/**
 * BrRegVarOrCanc2 test
 */
class BrRegVarOrCanc2Test extends AbstractBrRegVarOrCanc
{
    protected const NEW_TEXT = 'REGISTER';
    protected const VARY_TEXT = 'VARY';
    protected const CANCEL_TEXT = 'CANCEL';
    protected $bookmarkClass = BookmarkClass::class;
}
