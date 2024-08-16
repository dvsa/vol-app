<?php

/**
 * Cease Psv Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Discs;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Cease Psv Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CeasePsvDiscs extends AbstractCommand
{
    use Licence;
}
