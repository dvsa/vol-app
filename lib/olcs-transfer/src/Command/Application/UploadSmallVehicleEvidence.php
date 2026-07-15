<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\UploadedEvidence;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/small-vehicle-evidence-upload")
 * @Transfer\Method("PUT")
 */
final class UploadSmallVehicleEvidence extends AbstractIdWithVersionCommand
{
    use UploadedEvidence;
}
