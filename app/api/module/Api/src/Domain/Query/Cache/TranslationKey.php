<?php

namespace Dvsa\Olcs\Api\Domain\Query\Cache;

use Dvsa\Olcs\Transfer\FieldType\Traits\UniqueIdStringOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Translation key cache query
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationKey extends AbstractQuery
{
    use UniqueIdStringOptional;
}
