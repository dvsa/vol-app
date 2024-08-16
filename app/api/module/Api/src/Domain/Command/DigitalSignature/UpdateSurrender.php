<?php

namespace Dvsa\Olcs\Api\Domain\Command\DigitalSignature;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\DigitalSignature;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;

final class UpdateSurrender extends AbstractCommand
{
    use Licence;
    use DigitalSignature;
}
