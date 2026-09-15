<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\EvidenceUploadType;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/main-occupation-evidence")
 * @Transfer\Method("PUT")
 */
final class UpdateMainOccupationEvidence extends AbstractIdWithVersionCommand
{
    use EvidenceUploadType;
}
