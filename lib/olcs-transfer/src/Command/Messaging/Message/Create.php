<?php

namespace Dvsa\Olcs\Transfer\Command\Messaging\Message;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\CorrelationIdOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Messaging\Conversation;
use Dvsa\Olcs\Transfer\FieldType\Traits\Messaging\MessageContent;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/messages")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use Conversation;
    use MessageContent;
    use CorrelationIdOptional;
}
