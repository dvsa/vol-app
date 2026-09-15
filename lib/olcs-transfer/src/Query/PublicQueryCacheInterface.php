<?php

namespace Dvsa\Olcs\Transfer\Query;

/**
 * IF THE RESULT OF A QUERY MAY CONTAIN PERSONAL OR SENSITIVE INFORMATION THEN IT MUST NOT IMPLEMENT THIS INTERFACE
 *
 * Implement this interface to signify data from a query can be cached unencrypted (avoids overhead)
 * Used for data that is public and/or non-sensitive - refdata, translations, categories, feature toggles etc.
 *
 * Only affects data cached by CacheableMediumTermQueryInterface and higher (Short term is cached per request only)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface PublicQueryCacheInterface
{
}
