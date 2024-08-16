<?php

/**
 * Allocate Candidate Permits
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class AllocateCandidatePermits extends AbstractCommand
{
    use Identity;
}
