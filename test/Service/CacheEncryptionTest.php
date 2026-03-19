<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Service;

use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Cache\Storage\Adapter\AdapterOptions;
use Laminas\Cache\Storage\StorageInterface;

class CacheEncryptionTest extends MockeryTestCase
{
    private $nodeKey = 'nodeKey_12345678901234567890abcd';
    private $sharedKey = 'sharedKey_234567890123456789abcd';
    private $nodeSuffix = 'nodeSuffix';
    private $cacheIdentifier = 'cacheIdentifier';

    /**
     * Test cache retrieval
     *
     * @dataProvider dpGetSetItemProvider
     */
    public function testGetItem($encryptionMode, $encryptionKey, $nodeSuffix): void
    {
        $unserialisedValue = new \stdClass();
        $cacheKey = $this->cacheIdentifier . $nodeSuffix;

        $cache = m::mock(StorageInterface::class);

        // Use a real encrypt cycle to produce a valid ciphertext
        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);

        // First set the item so we capture what the cache would store
        $storedValue = null;
        $cache->expects('getOptions->setTtl')->with(3600)->andReturn(m::mock(AdapterOptions::class));
        $cache->expects('setItem')->with($cacheKey, m::on(function ($val) use (&$storedValue) {
            $storedValue = $val;
            return true;
        }))->andReturnTrue();

        $sut->setItem($this->cacheIdentifier, $encryptionMode, $unserialisedValue);

        // Now test retrieval using the stored ciphertext
        $cache->expects('getItem')->with($cacheKey)->andReturn($storedValue);

        self::assertEquals($unserialisedValue, $sut->getItem($this->cacheIdentifier, $encryptionMode));
    }

    /**
     * Test getting a custom item (use translations as sample for config purposes)
     */
    public function testGetCustomItem(): void
    {
        $unserialisedValue = new \stdClass();
        $serialisedValue = igbinary_serialize($unserialisedValue);
        $identifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $uniqueId = 'uniqueid';
        $cacheKey = $identifier . $uniqueId . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('getItem')->with($cacheKey)->andReturn($serialisedValue);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);

        self::assertEquals($unserialisedValue, $sut->getCustomItem($identifier, $uniqueId));
    }

    public function testGetItemWhenItemNotFound(): void
    {
        $cacheKey = $this->cacheIdentifier . $this->nodeSuffix;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('getItem')->with($cacheKey)->andReturnNull();

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);

        self::assertNull($sut->getItem($this->cacheIdentifier, CacheEncryption::ENCRYPTION_MODE_NODE));
    }

    /**
     * Test setting a cache item
     *
     * @dataProvider dpGetSetItemProvider
     */
    public function testSetItem($encryptionMode, $encryptionKey, $nodeSuffix): void
    {
        $valueToBeEncrypted = new \stdClass();
        $cacheKey = $this->cacheIdentifier . $nodeSuffix;
        $ttl = 300;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('getOptions->setTtl')->with($ttl)->andReturn(m::mock(AdapterOptions::class));
        $cache->expects('setItem')->with($cacheKey, m::type('string'))->andReturnTrue();

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);

        self::assertTrue($sut->setItem($this->cacheIdentifier, $encryptionMode, $valueToBeEncrypted, $ttl));
    }

    public function dpGetSetItemProvider(): array
    {
        return [
            [CacheEncryption::ENCRYPTION_MODE_NODE, $this->nodeKey, $this->nodeSuffix],
            [CacheEncryption::ENCRYPTION_MODE_SHARED, $this->sharedKey, CacheEncryption::ENCRYPTION_SHARED_NODE_SUFFIX],
        ];
    }

    /**
     * Test setting an unencrypted item to the cache
     */
    public function testSetItemPublic(): void
    {
        $valueToBeEncrypted = new \stdClass();
        $serializedValue = igbinary_serialize($valueToBeEncrypted);
        $cacheKey = $this->cacheIdentifier . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX;
        $ttl = 300;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('getOptions->setTtl')->with($ttl)->andReturn(m::mock(AdapterOptions::class));
        $cache->expects('setItem')->with($cacheKey, $serializedValue)->andReturnTrue();

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertTrue($sut->setItem($this->cacheIdentifier, CacheEncryption::ENCRYPTION_MODE_PUBLIC, $valueToBeEncrypted, $ttl));
    }

    /**
     * Test setting a custom item (use translations as sample for config purposes)
     */
    public function testSetCustomItem(): void
    {
        $valueToBeEncrypted = new \stdClass();
        $serializedValue = igbinary_serialize($valueToBeEncrypted);
        $identifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $config = CacheEncryption::CUSTOM_CACHE_TYPE[CacheEncryption::TRANSLATION_KEY_IDENTIFIER];
        $uniqueId = 'uniqueid';
        $cacheKey = $identifier . $uniqueId . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('getOptions->setTtl')->with($config['ttl'])->andReturn(m::mock(AdapterOptions::class));
        $cache->expects('setItem')->with($cacheKey, $serializedValue)->andReturnTrue();

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertTrue($sut->setCustomItem($identifier, $valueToBeEncrypted, $uniqueId));
    }

    public function testRemoveCustomItem(): void
    {
        $identifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $uniqueId = 'uniqueid';
        $cacheKey = $identifier . $uniqueId . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('removeItem')->with($cacheKey)->andReturnTrue();

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertTrue($sut->removeCustomItem($identifier, $uniqueId));
    }

    public function testRemoveCustomItems(): void
    {
        $identifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $uniqueIds = ['uniqueId1', 'uniqueId2', 'uniqueId3'];

        $cacheKeysRemoved = [
            'uniqueId1' => $identifier . 'uniqueId1' . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX,
            'uniqueId2' => $identifier . 'uniqueId2' . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX,
            'uniqueId3' => $identifier . 'uniqueId3' . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX,
        ];

        $cache = m::mock(StorageInterface::class);
        $cache->expects('removeItems')->with($cacheKeysRemoved)->andReturn([]);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        $sut->removeCustomItems($identifier, $uniqueIds);
    }

    public function testRemoveCustomItemsMissingIds(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CacheEncryption::ERR_NO_IDS_TO_DELETE);
        $cache = m::mock(StorageInterface::class);
        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        $sut->removeCustomItems('cache key', []);
    }

    /**
     * @dataProvider dpHasItemProvider
     */
    public function testHasItem($hasItem, $encryptionMode, $nodeSuffix): void
    {
        $cacheKey = $this->cacheIdentifier . $nodeSuffix;
        $cache = m::mock(StorageInterface::class);
        $cache->expects('hasItem')->with($cacheKey)->andReturn($hasItem);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);

        self::assertEquals($sut->hasItem($this->cacheIdentifier, $encryptionMode), $hasItem);
    }

    public function dpHasItemProvider(): array
    {
        return [
            [true, CacheEncryption::ENCRYPTION_MODE_NODE, $this->nodeSuffix],
            [false, CacheEncryption::ENCRYPTION_MODE_NODE, $this->nodeSuffix],
            [true, CacheEncryption::ENCRYPTION_MODE_SHARED, CacheEncryption::ENCRYPTION_SHARED_NODE_SUFFIX],
            [false, CacheEncryption::ENCRYPTION_MODE_SHARED, CacheEncryption::ENCRYPTION_SHARED_NODE_SUFFIX],
            [true, CacheEncryption::ENCRYPTION_MODE_PUBLIC, CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX],
            [false, CacheEncryption::ENCRYPTION_MODE_PUBLIC, CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX],
        ];
    }

    /**
     * Test has custom item (use translations as sample for config purposes)
     *
     * @dataProvider dpTrueFalseProvider
     */
    public function testHasCustomItem($hasItem): void
    {
        $identifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $uniqueId = 'uniqueid';
        $cacheIdentifier = $identifier . $uniqueId . CacheEncryption::ENCRYPTION_PUBLIC_NODE_SUFFIX;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('hasItem')->with($cacheIdentifier)->andReturn($hasItem);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertEquals($sut->hasCustomItem($identifier, $uniqueId), $hasItem);
    }

    public function dpTrueFalseProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    public function testMissingCustomConfig(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('missing config for cache type missing_cache_type');

        $cache = m::mock(StorageInterface::class);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        $sut->hasCustomItem('missing_cache_type', '');
    }

    /**
     * Test that exception is thrown for missing encryption key
     */
    public function testMissingEncryptionKeyException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CacheEncryption::ERR_NO_KEY_AVAILABLE);
        $cache = m::mock(StorageInterface::class);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertTrue($sut->setItem($this->cacheIdentifier, 'made up encryption mode', 'value'));
    }

    /**
     * Test encrypt/decrypt round-trip produces original value
     *
     * @dataProvider dpGetSetItemProvider
     */
    public function testEncryptDecryptRoundTrip($encryptionMode, $encryptionKey, $nodeSuffix): void
    {
        $originalValue = ['key' => 'value', 'nested' => ['data' => true]];
        $cacheKey = $this->cacheIdentifier . $nodeSuffix;

        $storedValue = null;

        $cache = m::mock(StorageInterface::class);
        $cache->expects('getOptions->setTtl')->with(3600)->andReturn(m::mock(AdapterOptions::class));
        $cache->expects('setItem')->with($cacheKey, m::on(function ($val) use (&$storedValue) {
            $storedValue = $val;
            return true;
        }))->andReturnTrue();

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        $sut->setItem($this->cacheIdentifier, $encryptionMode, $originalValue);

        $cache->expects('getItem')->with($cacheKey)->andReturn($storedValue);

        self::assertEquals($originalValue, $sut->getItem($this->cacheIdentifier, $encryptionMode));
    }

    public function testGetCustomCacheIdentifierForCqrs(): void
    {
        $cache = m::mock(StorageInterface::class);
        $dto = m::mock(QueryContainerInterface::class);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        $map = CacheEncryption::QUERY_MAP;

        foreach ($map as $dtoClass => $cacheIdentifier) {
            $dto->expects('getDtoClassName')->andReturn($dtoClass);
            self::assertEquals($cacheIdentifier, $sut->getCustomCacheIdentifierForCqrs($dto));
        }

        $dto->expects('getDtoClassName')->andReturn('dto');

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertNull($sut->getCustomCacheIdentifierForCqrs($dto));
    }

    public function testGetCustomCacheIdentifierForCqrsWhenNull(): void
    {
        $cache = m::mock(StorageInterface::class);
        $dto = m::mock(QueryContainerInterface::class);
        $dto->expects('getDtoClassName')->andReturn('dto');

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertNull($sut->getCustomCacheIdentifierForCqrs($dto));
    }

    public function testGetQueryFromCustomIdentifier(): void
    {
        $cache = m::mock(StorageInterface::class);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        $map = CacheEncryption::QUERY_MAP;

        foreach ($map as $dtoClass => $cacheIdentifier) {
            self::assertEquals($dtoClass, $sut->getQueryFromCustomIdentifier($cacheIdentifier));
        }
    }

    public function testGetQueryFromCustomIdentifierWhenNull(): void
    {
        $cache = m::mock(StorageInterface::class);

        $sut = new CacheEncryption($cache, $this->nodeKey, $this->sharedKey, $this->nodeSuffix);
        self::assertNull($sut->getQueryFromCustomIdentifier('missing identifier'));
    }
}
