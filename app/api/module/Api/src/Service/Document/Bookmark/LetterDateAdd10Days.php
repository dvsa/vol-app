<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DateDelta;

/**
 * Letter date + 10 days class
 */
class LetterDateAdd10Days extends DateDelta
{
    public const DELTA = "+10";
}
