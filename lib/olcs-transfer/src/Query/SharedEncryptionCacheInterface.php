<?php

namespace Dvsa\Olcs\Transfer\Query;

/**
 * Implement this interface to have the query result encrypted using the shared encryption key
 * Use when query is used by multiple node types, for instance it may be used by both selfserve and internal
 *
 * Only affects data cached by CacheableMediumTermQueryInterface and higher (Short term is cached per request only)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface SharedEncryptionCacheInterface
{
}
