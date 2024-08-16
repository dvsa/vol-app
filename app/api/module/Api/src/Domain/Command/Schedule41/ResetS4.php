<?php

/**
 * ResetS4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Schedule41;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Reset S4 record.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class ResetS4 extends AbstractCommand
{
    use Identity;
}
