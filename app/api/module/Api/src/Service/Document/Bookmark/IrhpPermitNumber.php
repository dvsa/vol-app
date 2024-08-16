<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpPermitBundle as Qry;

/**
 * IrhpPermitNumber
 */
class IrhpPermitNumber extends SingleValueAbstract
{
    public const FIELD  = 'permitNumber';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'irhpPermit';
    public const QUERY_CLASS = Qry::class;
}
