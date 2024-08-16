<?php

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Schedule41
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Schedule41 extends AbstractCommand
{
    use Identity;
}
