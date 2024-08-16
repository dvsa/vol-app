<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class BrTasNotified
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrTasNotified extends SingleValueAbstract
{
    public const FORMATTER = 'BrTasNotified';
    public const FIELD  = 'trafficAreas';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = DynamicBookmark::PARAM_BUSREG_ID;
    public const BUNDLE = ['trafficAreas'];
    public const QUERY_CLASS = Qry::class;
}
