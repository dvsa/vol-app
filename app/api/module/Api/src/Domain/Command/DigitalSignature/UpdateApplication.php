<?php

namespace Dvsa\Olcs\Api\Domain\Command\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\Traits\DigitalSignature;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;

final class UpdateApplication extends AbstractCommand
{
    use Application;
    use DigitalSignature;
}
