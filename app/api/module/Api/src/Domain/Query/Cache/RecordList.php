<?php

namespace Dvsa\Olcs\Api\Domain\Query\Cache;

use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

class RecordList extends AbstractQuery
{
    use IdentityString;
}
