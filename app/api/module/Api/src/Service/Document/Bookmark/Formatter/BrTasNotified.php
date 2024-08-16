<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * BrTasNotified formatter
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrTasNotified extends AbstractArrayList
{
    public const FORMAT = '%s';
    public const SEPARATOR = ', ';
    public const COLUMN = 'name';
}
