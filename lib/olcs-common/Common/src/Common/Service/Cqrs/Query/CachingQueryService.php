<?php

namespace Common\Service\Cqrs\Query;

use Common\Service\Cqrs\Exception\CacheTtlException;
use Common\Service\Cqrs\RecoverHttpClientExceptionTrait;
use Dvsa\Olcs\Transfer\Query\Cache\ById;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\CustomCacheableInterface;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption as CacheEncryptionService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;

/**
 * Class CachingQueryService
 * @package Common\Service\Cqrs\Query
 */
class CachingQueryService implements QueryServiceInterface, \Psr\Log\LoggerAwareInterface
{
    use \Psr\Log\LoggerAwareTrait;
    use RecoverHttpClientExceptionTrait;

    public const BACKEND_FAIL_MSG = 'Backend DB failure HTTP code: %s';

    public const CACHE_FAIL_MSG = 'Cache failure: %s';

    public const CACHE_LOCAL_SAVE_MSG = 'Storing in local cache: %s';

    public const CACHE_LOCAL_RETRIEVE_MSG = 'Fetching from local cache: %s';

    public const CACHE_PERSISTENT_SAVE_MSG = 'Storing in persistent cache with TTL of %u seconds: %s';

    public const CACHE_PERSISTENT_RETRIEVE_MSG = 'Fetching from persistent cache: %s';

    public const CACHE_ENCRYPTION_MODE_MSG = 'Using encryption mode: %s';

    public const CACHE_CUSTOM_CONFIG_MISSING_MSG = 'Custom cache config missing for: %s';

    public const MISSING_TTL_INTERFACE_TYPE = 'No TTL value found for this query';

    /** @var array */
    private $localCache;

    /**
     * Constructor
     *
     * @param QueryServiceInterface  $queryService Query service
     * @param CacheEncryptionService $cacheService Cache storage with automatic encryption built in
     * @param bool                   $enabled      Whether the cache is enabled
     * @param array                  $ttl          Ttl of the various cache types
     */
    public function __construct(private QueryServiceInterface $queryService, private CacheEncryptionService $cacheService, private AnnotationBuilder $annotationBuilder, private $enabled, private array $ttl)
    {
    }

    /**
     * Send a query to the backend
     *
     * @param QueryContainerInterface $query Query container
     *
     * @return \Common\Service\Cqrs\Response
     */
    #[\Override]
    public function send(QueryContainerInterface $query)
    {
        $this->queryService->setRecoverHttpClientException($this->getRecoverHttpClientException());

        if (!$this->enabled) {
            return $this->queryService->send($query);
        }

        /**
         * @todo we can't switch isCustomCacheable on as yet - before we do, there's a couple of issues to sort with
         * AbstractInternalController re: table sorting and the need to mimic a CQRS response for existing queries
         * Other than that it all works and there's code in the backend that should work immediately
         *
         * "custom cache" matches the naming scheme used elsewhere. In effect this means data we've added into the cache
         * which isn't in our traditional CQRS cache format and therefore can be accessed from all nodes (optionally)
         * and make use of encryption where necessary
         */
        if ($query->isCustomCacheable()) {
            //find the custom cache identifier for the query
            $cacheIdentifier = $this->cacheService->getCustomCacheIdentifierForCqrs($query);

            if ($cacheIdentifier !== null) {
                $dto = $query->getDto();
                $uniqueId = '';

                //this is done to quickly give us functionality for 99% of existing queries - VOL is a CRUD app
                if (method_exists($dto, 'getId')) {
                    $uniqueId = $dto->getId();
                }

                return $this->handleCustomCache($cacheIdentifier, $uniqueId);
            }

            //log an error for the missing config, but we should be able to fall back to standard CQRS
            $this->logError(sprintf(self::CACHE_CUSTOM_CONFIG_MISSING_MSG, $cacheIdentifier));
        }

        if ($query->isPersistentCacheable()) {
            try {
                return $this->handlePersistentCache($query);
            } catch (\Exception $e) {
                //error has occurred with the cache - log the error and retrieve fresh from the backend
                $this->logError(sprintf(self::CACHE_FAIL_MSG, $e->getMessage()));
            }
        }

        if ($query->isShortTermCacheable()) {
            return $this->handleLocalCache($query);
        }

        return $this->queryService->send($query);
    }

    /**
     * Feed a DTO through the annotation builder and then send the query
     *
     *
     * @return \Common\Service\Cqrs\Response
     */
    public function sendFromDto(QueryInterface $dto)
    {
        $query = $this->annotationBuilder->createQuery($dto);
        return $this->send($query);
    }

    /**
     * Retrieve data that is not found in the usual CQRS cache
     *
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function handleCustomCache(string $identifier, string $uniqueId = '')
    {
        try {
            if ($this->cacheService->hasCustomItem($identifier, $uniqueId)) {
                return $this->getCustomCache($identifier, $uniqueId);
            }
        } catch (\Exception $exception) {
            //error has occurred with the cache - log the error and retrieve fresh from the backend
            $this->logError(sprintf(self::CACHE_FAIL_MSG, $exception->getMessage()));
        }

        $queryParams = [
            'id' => $identifier,
            'uniqueId' => $uniqueId
        ];

        $dto = ById::create($queryParams);
        $response = $this->sendFromDto($dto);

        if ($response->isOk()) {
            return $response->getResult();
        }

        throw new \Exception(sprintf(self::BACKEND_FAIL_MSG, $response->getStatusCode()));
    }

    /**
     * Retrieve data that is not found in the usual CQRS cache
     *
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getCustomCache(string $identifier, string $uniqueId = '')
    {
        return $this->cacheService->getCustomItem($identifier, $uniqueId);
    }

    /**
     * Handle a query using a local cache, cache is a class property, therefore cache is valid only for current request
     *
     * @param QueryContainerInterface $query Query continer
     *
     * @return \Common\Service\Cqrs\Response
     */
    private function handleLocalCache(QueryContainerInterface $query)
    {
        $cacheIdentifier = $query->getCacheIdentifier();
        $dtoClassName = $query->getDtoClassName();

        if ($this->localCacheHasItem($cacheIdentifier)) {
            return $this->retrieveLocalCache($cacheIdentifier, $dtoClassName);
        }

        $result = $this->queryService->send($query);
        if ($result->isOk()) {
            $this->storeLocalCache($cacheIdentifier, $dtoClassName, $result);
        }

        return $result;
    }

    /**
     * Check if the local cache has the item
     *
     *
     */
    private function localCacheHasItem(string $cacheIdentifier): bool
    {
        return isset($this->localCache[$cacheIdentifier]);
    }

    /**
     * Retrieve a record from the local cache
     *
     *
     * @return mixed
     */
    private function retrieveLocalCache(string $cacheIdentifier, string $dtoClassName)
    {
        $this->logMessage(sprintf(self::CACHE_LOCAL_RETRIEVE_MSG, $dtoClassName));
        return $this->localCache[$cacheIdentifier];
    }

    /**
     * Retrieve a record from the local cache
     *
     *
     */
    private function storeLocalCache(string $cacheIdentifier, string $dtoClassName, $result): void
    {
        $this->logMessage(sprintf(self::CACHE_LOCAL_SAVE_MSG, $dtoClassName));
        $this->localCache[$cacheIdentifier] = $result;
    }

    /**
     * Handle a query using cache storage, lifetime of cache is from settings
     *
     * @param QueryContainerInterface $query Query container
     *
     * @return \Common\Service\Cqrs\Response
     */
    private function handlePersistentCache(QueryContainerInterface $query)
    {
        $cacheIdentifier = $query->getCacheIdentifier();
        $dtoClassName = $query->getDtoClassName();

        //check the local cache first
        if ($this->localCacheHasItem($cacheIdentifier)) {
            return $this->retrieveLocalCache($cacheIdentifier, $dtoClassName);
        }

        $encryptionMode = $query->getEncryptionMode();
        $this->logMessage(sprintf(self::CACHE_ENCRYPTION_MODE_MSG, $encryptionMode));

        /**
         * see if the cache has the item
         * additionally checks if the information is available to the node where the cache is running
         */
        $success = $this->cacheService->hasItem($cacheIdentifier, $encryptionMode);

        if (!$success) {
            $result = $this->queryService->send($query);
            if ($result->isOk()) {
                //add the result to the local cache to avoid future trips on the same request
                $this->storeLocalCache($cacheIdentifier, $dtoClassName, $result);

                try {
                    $ttl = $this->getCacheTtl($query);
                } catch (CacheTtlException $e) {
                    $this->logError(sprintf(self::CACHE_FAIL_MSG, $e->getMessage()));
                    return $result;
                }

                $this->logMessage(sprintf(self::CACHE_PERSISTENT_SAVE_MSG, $ttl, $dtoClassName));

                $this->cacheService->setItem($cacheIdentifier, $encryptionMode, $result, $ttl);
            }
        } else {
            $this->logMessage(sprintf(self::CACHE_PERSISTENT_RETRIEVE_MSG, $dtoClassName));
            $result = $this->cacheService->getItem($cacheIdentifier, $encryptionMode);

            //add the result to the local cache to avoid future trips on the same request
            $this->storeLocalCache($cacheIdentifier, $dtoClassName, $result);
        }

        return $result;
    }

    /**
     * Get the cache ttl depending on the query type
     *
     *
     * @throws CacheTtlException
     */
    private function getCacheTtl(QueryContainerInterface $query): int
    {
        if ($query->isMediumTermCacheable() && isset($this->ttl[CacheableMediumTermQueryInterface::class])) {
            return $this->ttl[CacheableMediumTermQueryInterface::class];
        }

        if ($query->isLongTermCacheable() && isset($this->ttl[CacheableLongTermQueryInterface::class])) {
            return $this->ttl[CacheableLongTermQueryInterface::class];
        }

        throw new CacheTtlException(self::MISSING_TTL_INTERFACE_TYPE);
    }

    /**
     * Log a message to the injected logger
     *
     * @param string $message Message to log
     */
    private function logMessage($message): void
    {
        if ($this->logger) {
            $this->logger->debug($message);
        }
    }

    /**
     * Log error to the injected logger
     *
     * @param string $error Error to log
     */
    private function logError(string $error): void
    {
        if ($this->logger) {
            $this->logger->error($error);
        }
    }
}
