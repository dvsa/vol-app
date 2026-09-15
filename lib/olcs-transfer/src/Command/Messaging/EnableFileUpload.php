<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Messaging;

use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/messaging/fileuploads/enable")
 * @Transfer\Method("POST")
 */
final class EnableFileUpload extends AbstractCommand
{
    use Organisation;
}
