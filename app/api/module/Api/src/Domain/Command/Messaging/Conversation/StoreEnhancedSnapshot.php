<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Messaging\Conversation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class StoreEnhancedSnapshot extends AbstractCommand
{
    use Identity;
}
