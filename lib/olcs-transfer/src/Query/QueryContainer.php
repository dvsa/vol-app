<?php

namespace Dvsa\Olcs\Transfer\Query;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\InputFilter\InputFilterInterface;

/**
 * Query Container
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueryContainer implements QueryContainerInterface
{
    protected $routeName;

    protected $hasValidated = false;

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * @var QueryInterface
     */
    protected $dto;

    #[\Override]
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    #[\Override]
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Can the data come directly from the Redis cache
     *
     * @return bool
     */
    #[\Override]
    public function isCustomCacheable(): bool
    {
        return ($this->dto instanceof CustomCacheableInterface);
    }

    /**
     * Can the DTO be cached for short term
     *
     * @return bool
     */
    #[\Override]
    public function isShortTermCacheable()
    {
        return ($this->dto instanceof CacheableShortTermQueryInterface);
    }

    /**
     * Can the DTO be cached for medium term
     *
     * @return bool
     */
    #[\Override]
    public function isMediumTermCacheable()
    {
        return ($this->dto instanceof CacheableMediumTermQueryInterface);
    }

    /**
     * Can the DTO be cached for long term?
     *
     * @return bool
     */
    #[\Override]
    public function isLongTermCacheable(): bool
    {
        return ($this->dto instanceof CacheableLongTermQueryInterface);
    }

    /**
     * Can the DTO be cached in the persistent cache?
     *
     * @return bool
     */
    #[\Override]
    public function isPersistentCacheable(): bool
    {
        return $this->isLongTermCacheable() || $this->isMediumTermCacheable();
    }

    /**
     * Whether the cached query response should be encrypted - see notes on PublicQueryCacheInterface
     *
     * @return bool
     */
    #[\Override]
    public function isPublicCacheable(): bool
    {
        return ($this->dto instanceof PublicQueryCacheInterface);
    }

    /**
     * whether to encrypt and decrypt using the shared encryption key - see notes on SharedEncryptionCacheInterface
     *
     * @return bool
     */
    #[\Override]
    public function isSharedEncryptionCacheable(): bool
    {
        return ($this->dto instanceof SharedEncryptionCacheInterface);
    }

    /**
     * Is query should use stream
     *
     * @return bool
     */
    #[\Override]
    public function isStream()
    {
        return ($this->dto instanceof StreamInterface);
    }

    /**
     * Get the identifier used to cache the DTO with
     *
     * @return string
     */
    public function getCacheIdentifier()
    {
        $dtoClassName = $this->dto::class;
        $jsonData = json_encode($this->dto->getArrayCopy());

        return md5($dtoClassName . '-' . $jsonData);
    }

    #[\Override]
    public function setDto(QueryInterface $dto)
    {
        $this->dto = $dto;
    }

    #[\Override]
    public function getDto()
    {
        return $this->dto;
    }

    /**
     * Get the class name of the current DTO
     *
     * @return string
     */
    #[\Override]
    public function getDtoClassName(): string
    {
        return $this->dto::class;
    }

    #[\Override]
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    #[\Override]
    public function getRouteName()
    {
        return $this->routeName;
    }

    #[\Override]
    public function isValid()
    {
        $this->hasValidated = true;

        $this->inputFilter->setData($this->dto->getArrayCopy());
        $this->dto->exchangeArray($this->inputFilter->getValues());

        return $this->inputFilter->isValid();
    }

    #[\Override]
    public function getMessages()
    {
        if ($this->hasValidated === false) {
            throw new \Exception('Validation has not yet occurred');
        }

        return $this->inputFilter->getMessages();
    }

    /**
     * Return the encryption mode to be used if this query is cached
     *
     * @return string
     */
    #[\Override]
    public function getEncryptionMode(): string
    {
        if ($this->isPublicCacheable()) {
            return CacheEncryption::ENCRYPTION_MODE_PUBLIC;
        }

        if ($this->isSharedEncryptionCacheable()) {
            return CacheEncryption::ENCRYPTION_MODE_SHARED;
        }

        return CacheEncryption::ENCRYPTION_MODE_NODE;
    }
}
