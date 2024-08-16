<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle as Qry;

/**
 * Interim Licence - Trailers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimTrailers extends SingleValueAbstract
{
    public const SRCH_VAL_KEY = 'application';
    public const FIELD = 'interimAuthTrailers';
    public const DEFAULT_VALUE = 0;
    public const QUERY_CLASS = Qry::class;
}
