<?php

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BrRegNo extends SingleValueAbstract
{
    public const FORMATTER = null;
    public const FIELD  = 'regNo';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'busRegId';
    public const QUERY_CLASS = Qry::class;
}
