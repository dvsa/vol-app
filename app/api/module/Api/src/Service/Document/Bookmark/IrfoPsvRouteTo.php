<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrfoPsvAuthBundle as Qry;

/**
 * IrfoPsvRouteTo
 */
class IrfoPsvRouteTo extends SingleValueAbstract
{
    public const FIELD  = 'serviceRouteTo';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'irfoPsvAuth';
    public const QUERY_CLASS = Qry::class;
}
