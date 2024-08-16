<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\HearingBundle as Qry;

/**
 * Hearingtime
 */
class Hearingtime extends SingleValueAbstract
{
    public const FORMATTER = 'Time';
    public const FIELD  = 'hearingDate';
    public const SRCH_FLD_KEY = 'case';
    public const SRCH_VAL_KEY = 'case';
    public const QUERY_CLASS = Qry::class;
}
