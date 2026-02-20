<?php

/**
 * AutoGrant
 *
 * @author Auto-grant feature
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * AutoGrant
 */
final class AutoGrant extends AbstractCommand
{
    use Identity;
}
