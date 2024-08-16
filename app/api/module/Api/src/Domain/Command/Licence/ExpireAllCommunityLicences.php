<?php

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * ExpireAllCommunityLicences
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ExpireAllCommunityLicences extends AbstractCommand
{
    use Identity;
}
