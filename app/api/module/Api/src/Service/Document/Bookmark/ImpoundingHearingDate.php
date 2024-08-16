<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\ImpoundingBundle as Qry;

/**
 * Impounding Hearing Date
 */
class ImpoundingHearingDate extends SingleValueAbstract
{
    public const FORMATTER = 'Date';
    public const FIELD  = 'hearingDate';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'impounding';
    public const QUERY_CLASS = Qry::class;
}
