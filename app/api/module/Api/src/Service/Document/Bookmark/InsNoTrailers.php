<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * InsNoTrailers bookmark - number of weeks between trailer inspections
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsNoTrailers extends SingleValueAbstract
{
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'safetyInsTrailers';
    public const QUERY_CLASS = Qry::class;
}
