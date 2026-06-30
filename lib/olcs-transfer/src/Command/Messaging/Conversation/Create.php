<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Messaging\Conversation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CorrelationIdOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Messaging\MessageContent;
use Dvsa\Olcs\Transfer\FieldType\Traits\Messaging\MessageSubject;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/conversations")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use MessageContent;
    use MessageSubject;
    use LicenceOptional;
    use ApplicationOptional;
    use CorrelationIdOptional;
}
