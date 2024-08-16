<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DateDelta;

/**
 * Letter date + 28 days class
 */
class LetterDateAdd28Days extends DateDelta
{
    public const DELTA = "+28";
}
