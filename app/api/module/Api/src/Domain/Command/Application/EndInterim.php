<?php

/**
 * End interim
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * End interim
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EndInterim extends AbstractCommand
{
    use Identity;
}
