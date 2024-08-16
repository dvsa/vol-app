<?php

namespace Dvsa\Olcs\Api\Domain\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Class StoreSnapshot
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class StoreSnapshot extends AbstractCommand
{
    use Identity;
}
