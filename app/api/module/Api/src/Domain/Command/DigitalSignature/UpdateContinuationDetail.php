<?php

namespace Dvsa\Olcs\Api\Domain\Command\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\Traits\ContinuationDetail;
use Dvsa\Olcs\Api\Domain\Command\Traits\DigitalSignature;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class UpdateContinuationDetail extends AbstractCommand
{
    use ContinuationDetail;
    use DigitalSignature;
}
