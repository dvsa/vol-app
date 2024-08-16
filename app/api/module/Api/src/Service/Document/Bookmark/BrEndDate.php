<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class BrEndDate
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrEndDate extends SingleValueAbstract
{
    public const DEFAULT_VALUE = 'N/A';
    public const FORMATTER = 'Date';
    public const FIELD  = 'endDate';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'busRegId';
    public const QUERY_CLASS = Qry::class;
}
