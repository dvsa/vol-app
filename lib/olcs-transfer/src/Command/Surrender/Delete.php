<?php

namespace Dvsa\Olcs\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/licence/single/surrender")
 * @Transfer\Method("DELETE")
 */
class Delete extends AbstractDeleteCommand
{
    use Identity;
}
