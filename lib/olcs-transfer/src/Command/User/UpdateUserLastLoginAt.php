<?php

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\SecureToken;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/user/login/update-last-login")
 * @Transfer\Method("POST")
 */
final class UpdateUserLastLoginAt extends AbstractCommand
{
    use SecureToken;
}
