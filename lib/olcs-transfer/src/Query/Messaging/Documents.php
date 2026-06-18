<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\Messaging;

use Dvsa\Olcs\Transfer\FieldType\Traits\CorrelationIdOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/documents")
 */
class Documents extends AbstractQuery
{
    use CorrelationIdOptional;
}
