<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Base;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Date Delta bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class DateDelta extends StaticBookmark
{
    public const FORMAT = "d/m/Y";
    public const DELTA  = "+0";

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $timestamp = strtotime(static::DELTA . " days");
        return (new DateTime('@' . $timestamp))->format(self::FORMAT);
    }
}
