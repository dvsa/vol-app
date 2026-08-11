<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Variation;

use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Query\QueryContainer;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\SharedEncryptionCacheInterface;
use Dvsa\Olcs\Transfer\Query\StreamInterface;
use Dvsa\Olcs\Transfer\Router\Query;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\QueryContainer::class)]
final class QueryContainerTest extends MockeryTestCase
{
    /** @var QueryContainer */
    private $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new QueryContainer();
    }

    public function testGetCacheIdentifier()
    {
        $mockDto = m::mock(QueryInterface::class);
        $mockDto->shouldReceive('getArrayCopy')->with()->once()->andReturn(['foo' => 'bar']);
        $this->sut->setDto($mockDto);

        $expected = md5($mockDto::class . '-' . json_encode(['foo' => 'bar']));

        $this->assertSame($expected, $this->sut->getCacheIdentifier());
    }

    public function testGetDtoClassName()
    {
        $dto = m::mock(QueryInterface::class);
        $className = $dto::class;
        $this->sut->setDto($dto);

        $this->assertEquals($className, $this->sut->getDtoClassName());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetCacheEncryptionMode')]
    public function testGetCacheEncryptionMode($dto, $encryptionMode)
    {
        $sut = new QueryContainer();
        $sut->setDto($dto);

        $this->assertEquals($encryptionMode, $sut->getEncryptionMode());
    }

    public static function dpGetCacheEncryptionMode(): \Iterator
    {
        yield [
            m::mock(QueryInterface::class . ',' . PublicQueryCacheInterface::class),
            CacheEncryption::ENCRYPTION_MODE_PUBLIC
        ];
        yield [
            m::mock(QueryInterface::class . ',' . SharedEncryptionCacheInterface::class),
            CacheEncryption::ENCRYPTION_MODE_SHARED
        ];
        yield [
            m::mock(QueryInterface::class),
            CacheEncryption::ENCRYPTION_MODE_NODE
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsX')]
    public function testIsX($dto, $expect)
    {
        $this->sut = new QueryContainer();
        $this->sut->setDto($dto);

        $this->assertEquals($expect['isShortCache'], $this->sut->isShortTermCacheable());
        $this->assertEquals($expect['isMediumCache'], $this->sut->isMediumTermCacheable());
        $this->assertEquals($expect['isLongCache'], $this->sut->isLongTermCacheable());
        $this->assertEquals($expect['isPersistentCache'], $this->sut->isPersistentCacheable());
        $this->assertEquals($expect['isPublicCache'], $this->sut->isPublicCacheable());
        $this->assertEquals($expect['isSharedEncryptionCache'], $this->sut->isSharedEncryptionCacheable());
        $this->assertEquals($expect['isStream'], $this->sut->isStream());
    }

    public static function dpTestIsX(): \Iterator
    {
        yield [
            'dto' => m::mock(
                implode(
                    ',',
                    [
                        QueryInterface::class,
                        CacheableShortTermQueryInterface::class,
                        CacheableMediumTermQueryInterface::class,
                        CacheableLongTermQueryInterface::class,
                        PublicQueryCacheInterface::class,
                        SharedEncryptionCacheInterface::class,
                        StreamInterface::class,
                    ]
                )
            ),
            'expect' => [
                'isShortCache' => true,
                'isMediumCache' => true,
                'isLongCache' => true,
                'isPersistentCache' => true,
                'isPublicCache' => true,
                'isSharedEncryptionCache' => true,
                'isStream' => true,
            ],
        ];
        yield [
            'dto' => m::mock(QueryInterface::class . ',' . StreamInterface::class),
            'expect' => [
                'isShortCache' => false,
                'isMediumCache' => false,
                'isLongCache' => false,
                'isPersistentCache' => false,
                'isPublicCache' => false,
                'isSharedEncryptionCache' => false,
                'isStream' => true,
            ],
        ];
        yield [
            'dto' => m::mock(QueryInterface::class . ',' . CacheableShortTermQueryInterface::class),
            'expect' => [
                'isShortCache' => true,
                'isMediumCache' => false,
                'isLongCache' => false,
                'isPersistentCache' => false,
                'isPublicCache' => false,
                'isSharedEncryptionCache' => false,
                'isStream' => false,
            ],
        ];
        yield [
            'dto' => m::mock(QueryInterface::class . ',' . CacheableMediumTermQueryInterface::class),
            'expect' => [
                'isShortCache' => false,
                'isMediumCache' => true,
                'isLongCache' => false,
                'isPersistentCache' => true,
                'isPublicCache' => false,
                'isSharedEncryptionCache' => false,
                'isStream' => false,
            ],
        ];
        yield [
            'dto' => m::mock(QueryInterface::class . ',' . CacheableLongTermQueryInterface::class),
            'expect' => [
                'isShortCache' => false,
                'isMediumCache' => false,
                'isLongCache' => true,
                'isPersistentCache' => true,
                'isPublicCache' => false,
                'isSharedEncryptionCache' => false,
                'isStream' => false,
            ],
        ];
        yield [
            'dto' => m::mock(QueryInterface::class . ',' . SharedEncryptionCacheInterface::class),
            'expect' => [
                'isShortCache' => false,
                'isMediumCache' => false,
                'isLongCache' => false,
                'isPersistentCache' => false,
                'isPublicCache' => false,
                'isSharedEncryptionCache' => true,
                'isStream' => false,
            ],
        ];
        yield [
            'dto' => m::mock(QueryInterface::class . ',' . PublicQueryCacheInterface::class),
            'expect' => [
                'isShortCache' => false,
                'isMediumCache' => false,
                'isLongCache' => false,
                'isPersistentCache' => false,
                'isPublicCache' => true,
                'isSharedEncryptionCache' => false,
                'isStream' => false,
            ],
        ];
    }
}
