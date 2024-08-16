<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\EndDate;
use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\FieldType\Traits\StartDate;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

final class GenerateReport extends AbstractCommand
{
    use IdentityString;
    use StartDate;
    use EndDate;
    use User;
}
