<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cache\ById as Handler;
use Dvsa\Olcs\Transfer\Query\Cache\ById as ByIdQry;
use Dvsa\Olcs\Api\Domain\Query\Cache\TranslationKey as TranslationKeyQry;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Tests the cache handler calls the correct query (uses the translation key query as an example)
 *
 * @see Handler
 */
class ByIdTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(Handler::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockUser = m::mock(User::class);
        $mockUser->expects('isAnonymous')->andReturnTrue();

        $authService = m::mock(AuthorizationService::class);
        $authService->expects('getIdentity->getUser')->andReturn($mockUser);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => $authService,
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('shouldRegenProvider')]
    public function testHandleQuery(bool $shouldRegen): void
    {
        $cacheId = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $uniqueId = 'uniqueId';
        $cacheValue = 'cache value';

        $queryParams = [
            'id' => $cacheId,
            'uniqueId' => $uniqueId,
            'shouldRegen' => $shouldRegen,
        ];

        $queryHandler = m::mock(AbstractQueryHandler::class);

        $queryHandler->expects('handleQuery')
            ->with(m::type(TranslationKeyQry::class))
            ->andReturnUsing(function ($childQuery) use ($cacheId, $uniqueId, $cacheValue) {
                $this->assertEquals($uniqueId, $childQuery->getUniqueId());

                return $cacheValue;
            });

        $this->sut->expects('getQueryHandler')->withNoArgs()->andReturn($queryHandler);

        //cache isn't checked if $shouldRegen is true
        $this->mockedSmServices[CacheEncryption::class]
            ->expects('hasCustomItem')
            ->with($cacheId, $uniqueId)
            ->times($shouldRegen ? 0 : 1)
            ->andReturnFalse();

        $this->mockedSmServices[CacheEncryption::class]
            ->expects('setCustomItem')
            ->with($cacheId, $cacheValue, $uniqueId);

        $query = ByIdQry::create($queryParams);
        $this->assertEquals($cacheValue, $this->sut->handleQuery($query));
    }

    public static function shouldRegenProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testHandleQueryCacheExists(): void
    {
        $cacheId = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;
        $uniqueId = 'uniqueId';
        $cacheValue = 'cache value';

        $queryParams = [
            'id' => $cacheId,
            'uniqueId' => $uniqueId,
            'shouldRegen' => false,
        ];

        $this->mockedSmServices[CacheEncryption::class]
            ->expects('hasCustomItem')
            ->with($cacheId, $uniqueId)
            ->andReturnTrue();

        $this->mockedSmServices[CacheEncryption::class]
            ->expects('getCustomItem')
            ->with($cacheId, $uniqueId)
            ->andReturn($cacheValue);

        $query = ByIdQry::create($queryParams);
        $this->assertEquals($cacheValue, $this->sut->handleQuery($query));
    }

    public function testHandleQueryNoPermission(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Handler::MSG_PERMISSION_ERROR);

        $queryParams = ['id' => 'some key'];

        $query = ByIdQry::create($queryParams);
        $this->sut->handleQuery($query);
    }
}
