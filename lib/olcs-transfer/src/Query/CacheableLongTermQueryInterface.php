<?php

namespace Dvsa\Olcs\Transfer\Query;

/**
 * Implement this interface to signify data from a query can be cached in the long term cache
 * Examples of such data include data which never changes, such as traffic areas, or data where we can expire the
 * cache as and when needed, such as translations data
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface CacheableLongTermQueryInterface
{
}
