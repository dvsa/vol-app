<?php

namespace CommonTest\Common\Service\Cqrs\Query;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Cqrs\Query\QueryServiceInterface;
use Dvsa\Olcs\Transfer\Query\Cache\ById;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameter;
use Dvsa\Olcs\Transfer\Service\CacheEncryption as CacheEncryptionService;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;

/**
 * @covers Common\Service\Cqrs\Query\CachingQueryService
 */
class CachingQueryServiceTest extends MockeryTestCase
{
    /** @var QueryContainerInterface | m\MockInterface */
    private $mockQuery;

    /** @var QueryServiceInterface | m\MockInterface */
    private $mockQS;

    /** @var CacheEncryptionService | m\MockInterface */
    private $mockCache;

    /** @var AnnotationBuilder | m\MockInterface */
    private $mockAnnotationBuilder;

    /** @var m\MockInterface */
    private $mockResult;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockQuery = m::mock(QueryContainerInterface::class);
        $this->mockCache = m::mock(CacheEncryptionService::class);
        $this->mockAnnotationBuilder = m::mock(AnnotationBuilder::class);

        $this->mockResult = m::mock(\Common\Service\Cqrs\Response::class);

        $this->mockQS = m::mock(QueryServiceInterface::class);
        $this->mockQS
            ->shouldReceive('setRecoverHttpClientException')
            ->shouldReceive('send')
            ->with($this->mockQuery)
            ->andReturn($this->mockResult);
    }

    public function testHandleCacheDisabled(): void
    {
        $sut = new CachingQueryService($this->mockQS, $this->mockCache, $this->mockAnnotationBuilder, false, $this->ttlValues());

        static::assertSame($this->mockResult, $sut->send($this->mockQuery));
    }

    public function testHandleCustomCache(): void
    {
        $identifier = 'identifier';
        $uniqueId = 'unique id';
        $cacheResult = 'result';

        $this->mockCache->expects('hasCustomItem')->with($identifier, $uniqueId)->andReturnTrue();
        $this->mockCache->expects('getCustomItem')->with($identifier, $uniqueId)->andReturn($cacheResult);

        $sut = new CachingQueryService($this->mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        self::assertEquals($cacheResult, $sut->handleCustomCache($identifier, $uniqueId));
    }

    public function testCustomCacheMissingThenLoadFromDb(): void
    {
        $identifier = 'identifier';
        $uniqueId = 'unique id';
        $cacheResult = 'result';
        $dto = m::mock(SystemParameter::class);
        $dto->expects('getId')->withNoArgs()->andReturn($uniqueId);

        $cqrsQueryContainer = m::mock(QueryContainerInterface::class);
        $cqrsQueryContainer->expects('isCustomCacheable')->withNoArgs()->andReturnTrue();
        $cqrsQueryContainer->expects('getDto')->withNoArgs()->andReturn($dto);
        $cqrsQueryContainer->expects('isPersistentCacheable')->never();
        $cqrsQueryContainer->expects('isShortTermCacheable')->never();

        $this->mockCache->expects('getCustomCacheIdentifierForCqrs')->with($cqrsQueryContainer)->andReturn($identifier);
        $this->mockCache->expects('hasCustomItem')->with($identifier, $uniqueId)->andReturnFalse();

        $cacheCqrsQueryContainer = m::mock(QueryContainerInterface::class);
        $cacheCqrsQueryContainer->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $cacheCqrsQueryContainer->expects('isPersistentCacheable')->withNoArgs()->andReturnFalse();
        $cacheCqrsQueryContainer->expects('isShortTermCacheable')->withNoArgs()->andReturnFalse();

        $this->mockAnnotationBuilder->expects('createQuery')
            ->with(m::type(ById::class))
            ->andReturn($cacheCqrsQueryContainer);

        $response = m::mock(Response::class);
        $response->expects('isOk')->withNoArgs()->andReturnTrue();
        $response->expects('getResult')->withNoArgs()->andReturn($cacheResult);

        $mockQS = m::mock(QueryServiceInterface::class);
        $mockQS->expects('setRecoverHttpClientException')->times(2);
        $mockQS->expects('send')->with($cacheCqrsQueryContainer)->andReturn($response);

        $sut = new CachingQueryService($mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        self::assertEquals($cacheResult, $sut->send($cqrsQueryContainer));
    }

    public function testCustomCacheExceptionThenDbFail(): void
    {
        $httpFailureCode = 418;
        $expectedMsg = sprintf(CachingQueryService::BACKEND_FAIL_MSG, $httpFailureCode);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMsg);

        $identifier = 'identifier';
        $uniqueId = 'unique id';
        $cacheResult = 'result';

        $cqrsQueryContainer = m::mock(QueryContainerInterface::class);
        $cqrsQueryContainer->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $cqrsQueryContainer->expects('isPersistentCacheable')->withNoArgs()->andReturnFalse();
        $cqrsQueryContainer->expects('isShortTermCacheable')->withNoArgs()->andReturnFalse();

        $this->mockCache->expects('hasCustomItem')->with($identifier, $uniqueId)->andReturnTrue();
        $this->mockCache->expects('getCustomItem')->with($identifier, $uniqueId)->andThrow(new \Exception());

        $this->mockAnnotationBuilder->expects('createQuery')
            ->with(m::type(ById::class))
            ->andReturn($cqrsQueryContainer);

        $response = m::mock(Response::class);
        $response->expects('isOk')->withNoArgs()->andReturnFalse();
        $response->expects('getStatusCode')->withNoArgs()->andReturn($httpFailureCode);

        $mockQS = m::mock(QueryServiceInterface::class);
        $mockQS->expects('setRecoverHttpClientException');
        $mockQS->expects('send')->with($cqrsQueryContainer)->andReturn($response);

        $sut = new CachingQueryService($mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        self::assertEquals($cacheResult, $sut->handleCustomCache($identifier, $uniqueId));
    }

    public function testSendWithCustomCache(): void
    {
        $identifier = 'identifier';
        $uniqueId = '';
        $cacheResult = 'result';
        $dto = m::mock(QueryInterface::class);

        $cqrsQueryContainer = m::mock(QueryContainerInterface::class);
        $cqrsQueryContainer->expects('isCustomCacheable')->withNoArgs()->andReturnTrue();
        $cqrsQueryContainer->expects('getDto')->withNoArgs()->andReturn($dto);
        $cqrsQueryContainer->expects('isPersistentCacheable')->never();
        $cqrsQueryContainer->expects('isShortTermCacheable')->never();

        $this->mockCache->expects('getCustomCacheIdentifierForCqrs')->with($cqrsQueryContainer)->andReturn($identifier);
        $this->mockCache->expects('hasCustomItem')->with($identifier, $uniqueId)->andReturnTrue();
        $this->mockCache->expects('getCustomItem')->with($identifier, $uniqueId)->andReturn($cacheResult);

        $sut = new CachingQueryService($this->mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        self::assertEquals($cacheResult, $sut->send($cqrsQueryContainer));
    }

    public function testSendWithNoCache(): void
    {
        $this->mockQuery->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $this->mockQuery->expects('isPersistentCacheable')->withNoArgs()->andReturnFalse();
        $this->mockQuery->expects('isShortTermCacheable')->withNoArgs()->andReturnFalse();

        $sut = new CachingQueryService($this->mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        static::assertSame($this->mockResult, $sut->send($this->mockQuery));
    }

    public function testSendWithShortCacheNull(): void
    {
        $this->mockQuery->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $this->mockQuery->expects('isPersistentCacheable')->withNoArgs()->andReturnFalse();
        $this->mockQuery->expects('isShortTermCacheable')->withNoArgs()->andReturnTrue();
        $this->mockQuery->expects('getDtoClassName')->withNoArgs()->andReturn('dto_class_name');
        $this->mockQuery->expects('getCacheIdentifier')->withNoArgs()->andReturn('cache_key');

        $this->mockResult->expects('isOk')->withNoArgs()->andReturnFalse();

        $sut = new CachingQueryService($this->mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        static::assertSame($this->mockResult, $sut->send($this->mockQuery));
    }

    public function testSendWithShortCache(): void
    {
        $this->mockQuery->expects('isCustomCacheable')->withNoArgs()->twice()->andReturnFalse();
        $this->mockQuery->expects('isPersistentCacheable')->withNoArgs()->twice()->andReturnFalse();
        $this->mockQuery->expects('isShortTermCacheable')->withNoArgs()->twice()->andReturnTrue();
        $this->mockQuery->expects('getDtoClassName')->withNoArgs()->twice()->andReturn('dto_class_name');
        $this->mockQuery->expects('getCacheIdentifier')->withNoArgs()->twice()->andReturn('cache_key');

        $this->mockResult->expects('isOk')->withNoArgs()->andReturnTrue();

        $sut = new CachingQueryService($this->mockQS, $this->mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        $sut->send($this->mockQuery);
        $sut->send($this->mockQuery);
    }

    /**
     * Test exception is thrown when the query doesn't have any of the possible query interfaces
     */
    public function testPersistentCacheMissingQueryInterface(): void
    {
        $mockQuery = m::mock(QueryContainerInterface::class);
        $mockQuery->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $mockQuery->expects('isPersistentCacheable')->withNoArgs()->andReturnTrue();
        $mockQuery->expects('isMediumTermCacheable')->withNoArgs()->andReturnFalse();
        $mockQuery->expects('isLongTermCacheable')->withNoArgs()->andReturnFalse();
        $mockQuery->expects('getDtoClassName')->withNoArgs()->andReturn('dto_class_name');
        $mockQuery->expects('getCacheIdentifier')->withNoArgs()->andReturn('cache_key');
        $mockQuery->expects('getEncryptionMode')->withNoArgs()->andReturn('encryption_mode');

        $mockQS = m::mock(QueryServiceInterface::class);
        $mockQS->expects('setRecoverHttpClientException');
        $mockQS->expects('send')->with($mockQuery)->andReturn($this->mockResult);

        $this->mockResult->expects('isOk')->withNoArgs()->andReturnTrue();

        $mockCache = m::mock(CacheEncryptionService::class);
        $mockCache->expects('hasItem')->with('cache_key', 'encryption_mode')->andReturnFalse();

        $mockLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $mockLogger->expects('debug')->with('Using encryption mode: encryption_mode')->ordered();
        $mockLogger->expects('debug')->with('Storing in local cache: dto_class_name')->ordered();
        $mockLogger->expects('error')->with('Cache failure: No TTL value found for this query')->ordered();

        $sut = new CachingQueryService($mockQS, $mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        $sut->setLogger($mockLogger);

        self::assertSame($this->mockResult, $sut->send($mockQuery));
    }

    /**
     * When the persistent cache is not populated
     *
     * Query is sent to the backend
     * Persistent and local caches both populated with the result
     *
     * @dataProvider dpPersistentCacheNotPopulated
     */
    public function testPersistentCacheNotPopulated($isMediumTerm, $cacheTtl): void
    {
        $mockQuery = m::mock(QueryContainerInterface::class);
        $mockQuery->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $mockQuery->expects('isPersistentCacheable')->withNoArgs()->andReturnTrue();
        $mockQuery->expects('isMediumTermCacheable')->withNoArgs()->andReturn($isMediumTerm);
        $mockQuery->expects('isLongTermCacheable')->withNoArgs()->times($isMediumTerm ? 0 : 1)->andReturnTrue();
        $mockQuery->expects('isShortTermCacheable')->never();
        $mockQuery->expects('getDtoClassName')->withNoArgs()->andReturn('dto_class_name');
        $mockQuery->expects('getCacheIdentifier')->withNoArgs()->andReturn('cache_key');
        $mockQuery->expects('getEncryptionMode')->withNoArgs()->andReturn('encryption_mode');

        $mockQS = m::mock(QueryServiceInterface::class);
        $mockQS->expects('setRecoverHttpClientException');
        $mockQS->expects('send')->with($mockQuery)->andReturn($this->mockResult);

        $this->mockResult->expects('isOk')->withNoArgs()->andReturnTrue();

        $mockCache = m::mock(CacheEncryptionService::class);
        $mockCache->expects('hasItem')->with('cache_key', 'encryption_mode')->andReturnFalse();
        $mockCache->expects('setItem')->with('cache_key', 'encryption_mode', $this->mockResult, $cacheTtl)->andReturn();

        $mockLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $mockLogger->expects('debug')->with('Using encryption mode: encryption_mode')->ordered();
        $mockLogger->expects('debug')->with('Storing in local cache: dto_class_name')->ordered();
        $mockLogger->expects('debug')->with('Storing in persistent cache with TTL of ' . $cacheTtl . ' seconds: dto_class_name')->ordered();

        $sut = new CachingQueryService($mockQS, $mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        $sut->setLogger($mockLogger);

        self::assertSame($this->mockResult, $sut->send($mockQuery));
    }

    /**
     * @return (bool|int)[][]
     *
     * @psalm-return list{list{true, 300}, list{false, 43200}}
     */
    public function dpPersistentCacheNotPopulated(): array
    {
        return [
            [true, 300],
            [false, 43200],
        ];
    }

    /**
     * When a value is retrieved from the persistent cache, it is saved to the short term cache
     *
     * First checks the local cache (initially not present)
     * Second checks and retrieves from the persistent cache
     * Third checks retrieval from the short term cache
     */
    public function testRetrieveFromPersistentThenRetrieveFromLocal(): void
    {
        /**
         * Each test is called twice, except encryption as 2nd time we use local cache
         */
        $mockQuery = m::mock(QueryContainerInterface::class);
        $mockQuery->expects('isCustomCacheable')->withNoArgs()->times(2)->andReturnFalse();
        $mockQuery->expects('isPersistentCacheable')->withNoArgs()->twice()->andReturnTrue();
        $mockQuery->expects('isShortTermCacheable')->never();
        $mockQuery->expects('getDtoClassName')->withNoArgs()->twice()->andReturn('dto_class_name');
        $mockQuery->expects('getCacheIdentifier')->withNoArgs()->twice()->andReturn('cache_key');
        $mockQuery->expects('getEncryptionMode')->withNoArgs()->andReturn('encryption_mode');

        $mockQS = m::mock(QueryServiceInterface::class);
        $mockQS->expects('setRecoverHttpClientException')->twice();

        /**
         * The query is sent twice, but we only go once to the persistent cache
         */
        $mockCache = m::mock(CacheEncryptionService::class);
        $mockCache->expects('hasItem')->with('cache_key', 'encryption_mode')->andReturnTrue();
        $mockCache->expects('getItem')->with('cache_key', true)->andReturn($this->mockResult);

        $mockLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $mockLogger->expects('debug')->with('Using encryption mode: encryption_mode')->ordered();
        $mockLogger->expects('debug')->with('Fetching from persistent cache: dto_class_name')->ordered();
        $mockLogger->expects('debug')->with('Storing in local cache: dto_class_name')->ordered();
        $mockLogger->expects('debug')->with('Fetching from local cache: dto_class_name')->ordered();

        $sut = new CachingQueryService($mockQS, $mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        $sut->setLogger($mockLogger);

        /**
         * first goes to persistent, second to local (backed up by logging order above)
         */
        self::assertSame($this->mockResult, $sut->send($mockQuery));
        self::assertSame($this->mockResult, $sut->send($mockQuery));
    }

    /**
     * Check that if there's an exception from the cache, the query is still executed
     */
    public function testRetrieveFromPersistentWithException(): void
    {
        $mockQuery = m::mock(QueryContainerInterface::class);
        $mockQuery->expects('isCustomCacheable')->withNoArgs()->andReturnFalse();
        $mockQuery->expects('isPersistentCacheable')->withNoArgs()->andReturnTrue();
        $mockQuery->expects('isShortTermCacheable')->withNoArgs()->andReturnFalse();
        $mockQuery->expects('getDtoClassName')->withNoArgs()->andReturn('dto_class_name');
        $mockQuery->expects('getCacheIdentifier')->withNoArgs()->andReturn('cache_key');
        $mockQuery->expects('getEncryptionMode')->withNoArgs()->andReturn('encryption_mode');

        $mockQS = m::mock(QueryServiceInterface::class);
        $mockQS->expects('setRecoverHttpClientException');
        $mockQS->expects('send')->with($mockQuery)->andReturn($this->mockResult);

        $mockCache = m::mock(CacheEncryptionService::class);
        $mockCache->expects('hasItem')->with('cache_key', 'encryption_mode')->andReturnTrue();
        $mockCache->expects('getItem')->with('cache_key', true)->andThrow(new \Exception('exception_msg'));

        $mockLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $mockLogger->expects('debug')->with('Using encryption mode: encryption_mode')->ordered();
        $mockLogger->expects('debug')->with('Fetching from persistent cache: dto_class_name')->ordered();
        $mockLogger->expects('error')->with('Cache failure: exception_msg')->ordered();

        $sut = new CachingQueryService($mockQS, $mockCache, $this->mockAnnotationBuilder, true, $this->ttlValues());
        $sut->setLogger($mockLogger);

        self::assertSame($this->mockResult, $sut->send($mockQuery));
    }

    public function ttlValues(): array
    {
        return [
            CacheableMediumTermQueryInterface::class => 300,
            CacheableLongTermQueryInterface::class => 43200,
        ];
    }
}
