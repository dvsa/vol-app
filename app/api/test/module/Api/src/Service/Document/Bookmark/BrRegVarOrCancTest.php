<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegVarOrCanc as BookmarkClass;

/**
 * AbstractBrRegVarOrCanc test
 */
class BrRegVarOrCancTest extends AbstractBrRegVarOrCanc
{
    protected const NEW_TEXT = 'commence';
    protected const VARY_TEXT = 'vary';
    protected const CANCEL_TEXT = 'cancel';
    protected $bookmarkClass = BookmarkClass::class;
}
