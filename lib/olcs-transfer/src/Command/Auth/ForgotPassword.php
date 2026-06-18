<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\FieldType\Traits\Realm;
use Dvsa\Olcs\Transfer\FieldType\Traits\Username;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/auth/forgot-password")
 * @Transfer\Method("POST")
 */
final class ForgotPassword extends AbstractCommand
{
    use Username;
    use Realm;
}
