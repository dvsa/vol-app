<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Auth;

use Dvsa\Olcs\Transfer\FieldType\Traits\ChallengeSession;
use Dvsa\Olcs\Transfer\FieldType\Traits\NewPassword;
use Dvsa\Olcs\Transfer\FieldType\Traits\Username;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/auth/change-expired-password")
 * @Transfer\Method("POST")
 */
final class ChangeExpiredPassword extends AbstractCommand
{
    use NewPassword;
    use ChallengeSession;
    use Username;
}
