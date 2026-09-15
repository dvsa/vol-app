<?php

namespace Dvsa\Olcs\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\FieldType\Traits\Username;
use Dvsa\Olcs\Transfer\FieldType\Traits\Password;
use Dvsa\Olcs\Transfer\FieldType\Traits\Realm;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/auth/login")
 * @Transfer\Method("POST")
 */
final class Login extends AbstractCommand
{
    use Username;
    use Password;
    use Realm;
}
