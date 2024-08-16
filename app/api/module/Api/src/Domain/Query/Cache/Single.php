<?php

namespace Dvsa\Olcs\Api\Domain\Query\Cache;

use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\FieldType\Traits\UniqueIdStringOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

class Single extends AbstractQuery
{
    use IdentityString;
    use UniqueIdStringOptional;
}
