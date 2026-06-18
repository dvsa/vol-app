<?php

namespace Dvsa\Olcs\Transfer\Query;

/**
 * Implement this interface to signify data from a query can come directly from the Redis cache
 * This allows us to bypass the old CQRS caches entirely and brings many benefits
 * Data which doesn't exist in the Redis cache will need to be regenerated for this interface to be used
 * So ensure that the cache code on the API node includes config for this
 */
interface CustomCacheableInterface
{
}
