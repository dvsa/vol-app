<?php

/**
 * BrRouteNo
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle;

/**
 * BrRouteNo
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BrRouteNo extends SingleValueAbstract
{
    public const FORMATTER = null;
    public const FIELD  = 'routeNo';
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = 'busRegId';
    public const QUERY_CLASS = BusRegBundle::class;
}
