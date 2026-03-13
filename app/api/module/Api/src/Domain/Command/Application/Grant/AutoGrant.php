<?php

declare(strict_types=1);

/**
 * AutoGrant
 *
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class AutoGrant extends AbstractCommand
{
    use Identity;
}
