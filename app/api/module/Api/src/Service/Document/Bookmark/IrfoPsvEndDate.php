<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrfoPsvAuthBundle as Qry;

/**
 * IrfoPsvEndDate
 */
class IrfoPsvEndDate extends SingleValueAbstract
{
    public const FORMATTER = 'Date';
    public const FIELD  = 'expiryDate';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'irfoPsvAuth';
    public const QUERY_CLASS = Qry::class;
}
