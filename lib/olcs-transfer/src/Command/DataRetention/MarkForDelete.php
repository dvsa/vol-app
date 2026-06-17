<?php

namespace Dvsa\Olcs\Transfer\Command\DataRetention;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/data-retention/mark-for-delete")
 * @Transfer\Method("POST")
 */
final class MarkForDelete extends AbstractCommand
{
    use Ids;
}
