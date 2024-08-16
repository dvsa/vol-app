<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class BrOtherServiceNos
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrOtherServiceNos extends SingleValueAbstract
{
    public const FORMATTER = 'BrOtherServiceNos';
    public const FIELD  = 'otherServices';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = DynamicBookmark::PARAM_BUSREG_ID;
    public const BUNDLE = ['otherServices'];
    public const QUERY_CLASS = Qry::class;
}
