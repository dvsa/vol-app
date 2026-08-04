<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\FieldType\Traits\NewPassword;
use Dvsa\Olcs\Transfer\FieldType\Traits\Password;
use Dvsa\Olcs\Transfer\FieldType\Traits\Realm;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/auth/change-password")
 * @Transfer\Method("POST")
 */
final class ChangePassword extends AbstractCommand
{
    use Password;
    use NewPassword;
}
