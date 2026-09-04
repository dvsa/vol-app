<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LongText;

use Dvsa\Olcs\Api\Domain\QueryHandler\LongText\ByReferenceKey;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText as LongTextEntity;
use Dvsa\Olcs\Transfer\Query\LongText\ByReferenceKey as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

final class ByReferenceKeyTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByReferenceKey();
        $this->mockRepo('LongText', LongTextRepo::class);

        parent::setUp();
    }

    public function testItReturnsTheContentStoredAgainstTheKey(): void
    {
        $entity = m::mock(LongTextEntity::class);

        $this->repoMap['LongText']
            ->shouldReceive('fetchByReferenceKey')
            ->once()
            ->with('application-declaration-gv79-gb', 'en_GB')
            ->andReturn($entity);

        $result = $this->sut->handleQuery(
            Qry::create(['referenceKey' => 'application-declaration-gv79-gb']),
        );

        self::assertSame($entity, $result->getObject());
    }

    public function testItPassesTheRequestedLocaleThrough(): void
    {
        $entity = m::mock(LongTextEntity::class);

        $this->repoMap['LongText']
            ->shouldReceive('fetchByReferenceKey')
            ->once()
            ->with('application-declaration-gv79-gb', 'cy_NI')
            ->andReturn($entity);

        $result = $this->sut->handleQuery(Qry::create([
            'referenceKey' => 'application-declaration-gv79-gb',
            'locale' => 'cy_NI',
        ]));

        self::assertSame($entity, $result->getObject());
    }
}
