<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DateDelta;

/**
 * Today's date in words bookmark
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class TodaysDateSentence extends DateDelta
{
    public const FORMAT = "j F Y";
}
