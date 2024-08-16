<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\PiHearingBundle as Qry;

/**
 * PiHearingDate
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class PiHearingTime extends SingleValueAbstract
{
    public const FORMATTER = 'Time';
    public const FIELD  = 'hearingDate';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'hearing';
    public const QUERY_CLASS = Qry::class;
}
