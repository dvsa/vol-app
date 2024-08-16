<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class SendNewMessageNotificationToOperators extends AbstractCommand
{
    use Identity;
}
