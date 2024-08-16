<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * BrOtherServiceNos formatter
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrOtherServiceNos extends AbstractArrayList
{
    public const FORMAT = '(%s)';
    public const SEPARATOR = ', ';
    public const COLUMN = 'serviceNo';
}
