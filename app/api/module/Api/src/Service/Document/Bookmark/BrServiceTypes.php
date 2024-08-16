<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class BrServiceTypes
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrServiceTypes extends SingleValueAbstract
{
    public const FORMATTER = 'BrServiceTypes';
    public const FIELD  = 'busServiceTypes';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = DynamicBookmark::PARAM_BUSREG_ID;
    public const BUNDLE = ['busServiceTypes'];
    public const QUERY_CLASS = Qry::class;
}
