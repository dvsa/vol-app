<?php

namespace Dvsa\Olcs\Transfer\Service;

use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameter;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameterList;
use Laminas\Cache\Storage\Adapter\AdapterOptions;
use Laminas\Cache\Storage\StorageInterface;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

class CacheEncryption
{
    public const string ERR_NO_KEY_AVAILABLE = 'No encryption key available for this encryption mode';
    public const string ERR_NO_IDS_TO_DELETE = 'Please provide ids for the items being deleted';

    public const string ENCRYPTION_MODE_PUBLIC = 'encryption_public';
    public const string ENCRYPTION_MODE_SHARED = 'encryption_shared';
    public const string ENCRYPTION_MODE_NODE = 'encryption_node';

    public const string ENCRYPTION_PUBLIC_NODE_SUFFIX = 'public';
    public const string ENCRYPTION_SHARED_NODE_SUFFIX = 'shared';

    public const int TTL_2_MINUTES = 120;
    public const int TTL_60_DAYS = 5184000;
    public const int TTL_20_DAYS = 1728000;
    public const int TTL_1_DAY = 86400;

    public const string TRANSLATION_KEY_IDENTIFIER = 'translation_key';
    public const string TRANSLATION_REPLACEMENT_IDENTIFIER = 'translation_replacement';

    public const string SYS_PARAM_IDENTIFIER = 'sys_param';
    public const string SYS_PARAM_LIST_IDENTIFIER = 'sys_param_list';
    public const string USER_ACCOUNT_IDENTIFIER = 'user_account';
    public const string GENERIC_STORAGE_IDENTIFIER = 'storage';

    public const string SECRETS_MANAGER_IDENTIFIER = 'secretsmanager';

    /** @var string[] a list of caches held against a user id */
    public const array USER_CACHES = [
        self::USER_ACCOUNT_IDENTIFIER
    ];

    public const array QUERY_MAP = [
        SystemParameter::class => self::SYS_PARAM_IDENTIFIER,
        SystemParameterList::class => self::SYS_PARAM_LIST_IDENTIFIER,
    ];

    public const array CUSTOM_CACHE_TYPE = [
        self::GENERIC_STORAGE_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_SHARED,
            'ttl' => self::TTL_1_DAY,
        ],
        self::SYS_PARAM_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_PUBLIC,
            'ttl' => self::TTL_60_DAYS,
        ],
        self::SYS_PARAM_LIST_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_PUBLIC,
            'ttl' => self::TTL_60_DAYS,
        ],
        self::TRANSLATION_KEY_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_PUBLIC,
            'ttl' => self::TTL_60_DAYS,
        ],
        self::TRANSLATION_REPLACEMENT_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_PUBLIC,
            'ttl' => self::TTL_60_DAYS,
        ],
        self::USER_ACCOUNT_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_SHARED,
            'ttl' => self::TTL_2_MINUTES,
        ],
        self::SECRETS_MANAGER_IDENTIFIER => [
            'mode' => self::ENCRYPTION_MODE_SHARED,
            'ttl'  => self::TTL_20_DAYS
        ]

    ];

    public function __construct(
        private readonly StorageInterface $cache,
        private readonly string $nodeKey,
        private readonly string $sharedKey,
        private readonly string $nodeSuffix
    ) {
    }

    /**
     * Whether the cache has the requested item
     */
    public function hasItem(string $cacheIdentifier, string $encryptionMode): bool
    {
        return $this->cache->hasItem($cacheIdentifier . $this->getSuffix($encryptionMode));
    }

    /**
     * Whether a custom (non-CQRS) cache item exists
     *
     * @throws \Exception
     */
    public function hasCustomItem(string $cacheType, string $uniqueId = ''): bool
    {
        $cacheConfig = $this->getCustomCacheConfig($cacheType);
        return $this->hasItem($cacheType . $uniqueId, $cacheConfig['mode']);
    }

    /**
     * Remove an item from the cache, based on the encryption mode
     *
     * Public mode: value won't be encrypted
     * Shared mode: value will have been encrypted using a key shared between all nodes
     * Node specific mode: value will have been encrypted for a single group of nodes only e.g. ssweb, iuweb or api
     *
     * @throws \Exception
     */
    public function removeItem(string $cacheKey, string $encryptionMode): bool
    {
        $nodeSuffix = $this->getSuffix($encryptionMode);
        return $this->cache->removeItem($cacheKey . $nodeSuffix);
    }

    /**
     * Remove a custom (non CQRS) cache item
     *
     * @throws \Exception
     */
    public function removeCustomItem(string $cacheKey, string $uniqueId = ''): bool
    {
        $cacheConfig = $this->getCustomCacheConfig($cacheKey);
        $nodeSuffix = $this->getSuffix($cacheConfig['mode']);
        return $this->cache->removeItem($cacheKey . $uniqueId . $nodeSuffix);
    }

    /**
     * Remove a series of custom caches e.g. for a series of user ids
     * Note that the method expects that ids will be included, to delete a cache which isn't specific
     * to a user/licence etc, use the removeCustomItem method which allows a blank value for $uniqueId
     *
     * @throws \Exception
     */
    public function removeCustomItems(string $cacheKey, array $uniqueIds): array
    {
        if (empty($uniqueIds)) {
            throw new \Exception(self::ERR_NO_IDS_TO_DELETE);
        }

        $cacheKeys = [];
        $cacheConfig = $this->getCustomCacheConfig($cacheKey);
        $nodeSuffix = $this->getSuffix($cacheConfig['mode']);

        foreach ($uniqueIds as $uniqueId) {
            $cacheKeys[$uniqueId] = $cacheKey . $uniqueId . $nodeSuffix;
        }

        return $this->cache->removeItems($cacheKeys);
    }

    /**
     * Set an item to the cache, based on the encryption mode
     *
     * Public mode: value won't be encrypted
     * Shared mode: value will be encrypted using a key shared between all nodes
     * Node specific mode: value will be encrypted for a single group of nodes only e.g. ssweb, iuweb or api
     * TTL is specified in seconds - 3600 means a default of one hour
     *
     * @throws \Exception
     */
    public function setItem(string $cacheKey, string $encryptionMode, mixed $value, int $ttl = 3600): bool
    {
        $value = igbinary_serialize($value);

        //if the encryption mode for this query is public then it need not be encrypted
        if ($encryptionMode !== self::ENCRYPTION_MODE_PUBLIC) {
            $encryptionKey = $this->getEncryptionKey($encryptionMode);
            $value = $this->encrypt($encryptionKey, $value);
        }

        $nodeSuffix = $this->getSuffix($encryptionMode);
        $this->setTtlOption($ttl);

        return $this->cache->setItem($cacheKey . $nodeSuffix, $value);
    }

    /**
     * Set a custom (non-CQRS) cache, based on config for TTL and encryption mode.
     *
     * @param string $cacheType must exist in the config or exception will be thrown
     * @param mixed  $value     value to be set in the cache
     * @param string $uniqueId  optional suffix to add uniqueness, such as a translation locale or user id
     *
     * @throws \Exception
     */
    public function setCustomItem(string $cacheType, mixed $value, $uniqueId = ''): bool
    {
        $cacheConfig = $this->getCustomCacheConfig($cacheType);
        return $this->setItem($cacheType . $uniqueId, $cacheConfig['mode'], $value, $cacheConfig['ttl']);
    }

    /**
     * Retrieve an item from the cache
     *
     * @throws \Exception
     * @return mixed|null
     */
    public function getItem(string $cacheKey, string $encryptionMode)
    {
        $nodeSuffix = $this->getSuffix($encryptionMode);
        $cacheValue = $this->cache->getItem($cacheKey . $nodeSuffix);

        if (is_null($cacheValue)) {
            return null;
        }

        //if the encryption mode for this query is public then it won't have been encrypted
        if ($encryptionMode !== self::ENCRYPTION_MODE_PUBLIC) {
            $encryptionKey = $this->getEncryptionKey($encryptionMode);
            $cacheValue = $this->decrypt($encryptionKey, $cacheValue);
        }

        return is_null($cacheValue) ? null : igbinary_unserialize($cacheValue);
    }

    /**
     * Retrieve a custom (non-CQRS) cache based on the config
     *
     * @param string $cacheType must exist in the config or exception will be thrown
     * @param string $uniqueId  optional suffix to add uniqueness, such as a translation locale or user id
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getCustomItem(string $cacheType, string $uniqueId = '')
    {
        $cacheConfig = $this->getCustomCacheConfig($cacheType);
        return $this->getItem($cacheType . $uniqueId, $cacheConfig['mode']);
    }

    /**
     * @note This isn't a great way of going about this, but there isn't a way of doing it on the Laminas client and
     * would rather not extend it at this stage. By making the method private we make sure only the TTL passed through
     * when each item is set will be used
     *
     * @param int $ttl time in seconds
     *
     * @return AdapterOptions
     */
    private function setTtlOption(int $ttl): AdapterOptions
    {
        return $this->cache->getOptions()->setTtl($ttl);
    }

    /**
     * Encrypt a value prior to saving in the cache
     */
    private function encrypt(string $encryptionKey, ?string $value): string
    {
        $key = new EncryptionKey(new HiddenString($encryptionKey));
        return Crypto::encrypt(new HiddenString((string) $value), $key);
    }

    /**
     * Decrypt a value using the specified encryption key
     */
    private function decrypt(string $encryptionKey, ?string $encryptedValue): ?string
    {
        if (is_null($encryptedValue)) {
            return null;
        }

        $key = new EncryptionKey(new HiddenString($encryptionKey));
        return Crypto::decrypt($encryptedValue, $key)->getString();
    }

    /**
     * Get (and check validity of) config for a custom cache type
     *
     * @throws \Exception
     */
    private function getCustomCacheConfig($cacheType): array
    {
        if (!isset(self::CUSTOM_CACHE_TYPE[$cacheType])) {
            throw new \Exception('missing config for cache type ' . $cacheType);
        }

        return self::CUSTOM_CACHE_TYPE[$cacheType];
    }

    /**
     * Get the correct encryption key
     *
     * @throws \Exception
     */
    private function getEncryptionKey(string $encryptionMode): string
    {
        if ($encryptionMode === self::ENCRYPTION_MODE_SHARED) {
            return $this->sharedKey;
        }

        if ($encryptionMode === self::ENCRYPTION_MODE_NODE) {
            return $this->nodeKey;
        }

        throw new \Exception(self::ERR_NO_KEY_AVAILABLE);
    }

    /**
     * Get the correct suffix to use
     * (prevents the same data encrypted with a different key from having the same cache identifier)
     */
    private function getSuffix(string $encryptionMode): string
    {
        //if saving non sensitive information we mark it with the public suffix
        if ($encryptionMode === self::ENCRYPTION_MODE_PUBLIC) {
            return self::ENCRYPTION_PUBLIC_NODE_SUFFIX;
        }

        //if using the shared encryption, use the shared suffix, otherwise be specific to the node
        if ($encryptionMode === self::ENCRYPTION_MODE_SHARED) {
            return self::ENCRYPTION_SHARED_NODE_SUFFIX;
        }

        return $this->nodeSuffix;
    }

    /**
     * Gets the encryption key for this node
     */
    public function getNodeKey(): string
    {
        return $this->nodeKey;
    }

    /**
     * Gets the shared encryption key
     */
    public function getSharedKey(): string
    {
        return $this->sharedKey;
    }

    public function getNodeSuffix(): string
    {
        return $this->nodeSuffix;
    }

    public function getCustomCacheIdentifierForCqrs(QueryContainerInterface $queryContainer): ?string
    {
        $dtoClass = $queryContainer->getDtoClassName();
        return self::QUERY_MAP[$dtoClass] ?? null;
    }

    public function getQueryFromCustomIdentifier(string $customCacheKey): ?string
    {
        $map = array_flip(self::QUERY_MAP);
        return $map[$customCacheKey] ?? null;
    }
}
