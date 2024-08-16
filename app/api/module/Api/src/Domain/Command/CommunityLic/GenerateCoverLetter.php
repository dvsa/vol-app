<?php

namespace Dvsa\Olcs\Api\Domain\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Community Licence / Generate Cover Letter
 */
final class GenerateCoverLetter extends AbstractCommand
{
    use Licence;
    use User;
}
