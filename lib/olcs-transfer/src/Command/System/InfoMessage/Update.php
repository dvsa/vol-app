<?php

namespace Dvsa\Olcs\Transfer\Command\System\InfoMessage;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/system-info-message/single")
 * @Transfer\Method("PUT")
 */
class Update extends Create
{
    use Identity;
}
