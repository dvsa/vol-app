<?php

namespace Dvsa\Olcs\Api\Domain\Command\DigitalSignature;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ContinuationDetail;
use Dvsa\Olcs\Transfer\FieldType\Traits\DigitalSignature;

final class UpdateContinuationDetail extends AbstractCommand
{
    use ContinuationDetail;
    use DigitalSignature;
}
