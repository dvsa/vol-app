<?php

namespace Dvsa\Olcs\Api\Domain\Command\Schedule41;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * CancelS4 record.
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CancelS4 extends AbstractCommand
{
    use Identity;
}
