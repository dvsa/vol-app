<?php

namespace Dvsa\Olcs\Api\Domain\Command\DigitalSignature;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\FieldType\Traits\DigitalSignature;
use Dvsa\Olcs\Transfer\FieldType\Traits\TmVerifyRole;

final class UpdateTmApplication extends AbstractCommand
{
    use Application;
    use DigitalSignature;
    use TmVerifyRole;
}
