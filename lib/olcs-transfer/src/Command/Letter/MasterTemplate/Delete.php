<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/letter/master-template/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
