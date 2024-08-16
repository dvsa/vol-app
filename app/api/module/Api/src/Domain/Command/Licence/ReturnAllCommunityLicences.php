<?php

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * ReturnAllCommunityLicences
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class ReturnAllCommunityLicences extends AbstractCommand
{
    use Identity;
}
