<?php

namespace Dvsa\Olcs\Transfer\Command\Cache;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Cache Clear Command DTO
 *
 * @Transfer\RouteName("backend/cache-clear")
 * @Transfer\Method("POST")
 */
class Clear extends AbstractCommand
{
    /**
     * Generic CQRS query cache written by the front end applications.
     *
     * Feature toggle lookups live here, which is why clearing it is the option that makes a
     * toggle change visible without waiting out the TTL.
     */
    public const string NAMESPACE_CQRS = 'cqrs';

    /** Doctrine ORM metadata, query, result and hydration caches. */
    public const string NAMESPACE_DOCTRINE = 'doctrine';

    /** Cognito JSON Web Key Set cache - clearing it forces the next request to refetch. */
    public const string NAMESPACE_JWKS = 'jwks';

    /**
     * Every namespace this command will act on.
     *
     * This is the shared vocabulary for the three callers - the internal Clear cache page, the
     * batch:cache-clear CLI command and the API command handler that does the work. Keeping it
     * on the DTO means adding a cache type is a one line change rather than three lists drifting
     * apart in two packages.
     *
     * These are logical names, not Redis key prefixes. The handler resolves each to a pattern
     * using the API's own 'caches' configuration, so a pool that gets renamed (as doctrine-cache
     * does under the ORM 3 upgrade) needs no change here.
     *
     * @var string[]
     */
    public const array NAMESPACES = [
        CacheEncryption::USER_ACCOUNT_IDENTIFIER,
        CacheEncryption::SYS_PARAM_IDENTIFIER,
        CacheEncryption::SYS_PARAM_LIST_IDENTIFIER,
        CacheEncryption::TRANSLATION_KEY_IDENTIFIER,
        CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER,
        CacheEncryption::GENERIC_STORAGE_IDENTIFIER,
        CacheEncryption::SECRETS_MANAGER_IDENTIFIER,
        self::NAMESPACE_CQRS,
        self::NAMESPACE_DOCTRINE,
        self::NAMESPACE_JWKS,
    ];

    /**
     * Result flag carrying how many Redis keys the clear actually removed.
     *
     * The count is what tells a caller whether the clear did anything - reporting success off
     * the HTTP status alone cannot distinguish "cleared 400 keys" from "matched nothing".
     */
    public const string RESULT_FLAG_KEYS_DELETED = 'keysDeleted';

    /**
     * @Transfer\Optional()
     */
    protected ?bool $flushAll = null;

    /**
     * @Transfer\Optional()
     */
    protected ?string $namespace = null;

    /**
     * @Transfer\Optional()
     */
    protected ?string $pattern = null;

    /**
     * @Transfer\Optional()
     */
    protected ?bool $dryRun = null;

    /**
     * @return bool|null
     */
    public function getFlushAll(): ?bool
    {
        return $this->flushAll;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return bool|null
     */
    public function getDryRun(): ?bool
    {
        return $this->dryRun;
    }
}
