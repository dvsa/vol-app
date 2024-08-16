<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Formatter interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
interface FormatterInterface
{
    public static function format(array $data);
}
