<?php

/**
 * Review
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\TransportManagerApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\LoggerOmitResponseInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/transport-manager-application/single/review")
 */
class Review extends AbstractQuery implements LoggerOmitResponseInterface
{
    use Identity;
}
