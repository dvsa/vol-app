<?php

/**
 * @note this query is used as a backup when the Redis cache is unavailable. Therefore it also triggers
 * a rebuild of the cache in the backend. As such, please don't reuse this query for anything else :)
 *
 * In addition to the id of the cache there is also a uniqueId parameter which allows the cache to be tied to
 * something specific e.g. a locale or a user
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Cache;

use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\FieldType\Traits\UniqueIdStringOptional;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/cache")
 */
class ById extends AbstractQuery implements CacheableShortTermQueryInterface
{
    use IdentityString;
    use UniqueIdStringOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $shouldRegen = false;

    /**
     * Whether the cache should be regenerated before returning the value
     *
     * @return bool
     */
    public function getShouldRegen()
    {
        return $this->shouldRegen;
    }
}
