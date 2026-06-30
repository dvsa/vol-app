<?php

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/variation/single/grant-director-change")
 * @Transfer\Method("PUT")
 */
class GrantDirectorChange extends AbstractCommand
{
    use Identity;
}
