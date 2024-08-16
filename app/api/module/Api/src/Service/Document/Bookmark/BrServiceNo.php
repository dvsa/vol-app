<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class BrServiceNo
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrServiceNo extends SingleValueAbstract
{
    public const DEFAULT_VALUE = 'N/A';
    public const FIELD  = 'serviceNo';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'busRegId';
    public const QUERY_CLASS = Qry::class;
}
