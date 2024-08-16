<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Cache Aware Interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface CacheAwareInterface
{
    /**
     * @return void
     */
    public function setCache(CacheEncryption $cacheService): void;

    /**
     * @return CacheEncryption
     */
    public function getCache(): CacheEncryption;
}
