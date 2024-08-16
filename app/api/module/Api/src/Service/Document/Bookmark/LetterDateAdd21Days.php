<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DateDelta;

/**
 * Letter date + 21 days class
 */
class LetterDateAdd21Days extends DateDelta
{
    public const DELTA = "+21";
}
