<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence Vehicle Limit Bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleLimit extends SingleValueAbstract
{
    public const FORMATTER = null;
    public const SRCH_VAL_KEY = 'licence';
    public const FIELD = 'totAuthVehicles';
    public const QUERY_CLASS = Qry::class;
}
