<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Query\Cache;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\Cache\ById as CacheByIdQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Generic cache handler
 * This is intended to cover standard cases and to be kept simple
 * It is worth thinking carefully before hacking or extending this to cover edge cases
 * Best to have edge cases covered by bespoke code :)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ById extends AbstractQueryHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    public const MSG_PERMISSION_ERROR = 'You don\'t have permission to generate this cache';

    private $map = [
        CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER => Cache\Replacements::class,
        CacheEncryption::TRANSLATION_KEY_IDENTIFIER => Cache\TranslationKey::class,
        CacheEncryption::SYS_PARAM_IDENTIFIER => Cache\Single::class,
        CacheEncryption::SYS_PARAM_LIST_IDENTIFIER => Cache\RecordList::class,
    ];

    private $anonAllowedMap = [
        CacheEncryption::SYS_PARAM_IDENTIFIER,
        CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER,
        CacheEncryption::TRANSLATION_KEY_IDENTIFIER,
    ];

    /**
     * Generic cache handler
     * This is intended to cover standard cases and to be kept simple
     * It is worth thinking carefully before hacking or extending this to cover edge cases
     * Best to have edge cases covered by bespoke code :)
     *
     * @param QueryInterface|CacheByIdQry $query
     *
     * @return mixed
     * @throws \Exception
     */
    public function handleQuery(QueryInterface $query)
    {
        $cacheId = $query->getId();

        /**
         * This security check is already done at an individual handler level, but to be extra careful we also
         * maintain a list here of caches which anon users are allowed to access. This prevents accidental access
         * to caches if validation on individual handlers is misconfigured. It is likely we will need to do more work
         * as time goes by around who has permissions to access and generate caches etc.
         */
        if ($this->getCurrentUser()->isAnonymous() && !in_array($cacheId, $this->anonAllowedMap)) {
            throw new \Exception(self::MSG_PERMISSION_ERROR);
        }

        $uniqueId = $query->getUniqueId();
        $shouldRegen = $query->getShouldRegen();

        if (!$shouldRegen && $this->cacheService->hasCustomItem($cacheId, $uniqueId)) {
            return $this->cacheService->getCustomItem($cacheId, $uniqueId);
        }

        $queryDto = $this->map[$cacheId];
        $childQuery = $queryDto::create($query->getArrayCopy());

        $value = $this->getQueryHandler()->handleQuery($childQuery);

        //update the cache (note this is also relied upon for the generate cache command to work)
        $this->cacheService->setCustomItem($cacheId, $value, $uniqueId);

        return $value;
    }
}
