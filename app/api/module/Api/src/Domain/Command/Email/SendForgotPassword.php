<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Realm;

final class SendForgotPassword extends AbstractIdOnlyCommand
{
    use Realm;
}
