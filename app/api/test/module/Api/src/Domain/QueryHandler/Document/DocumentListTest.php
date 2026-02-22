<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\DocumentList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\DocumentSearchView as Repo;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Doctrine\ORM\Query;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Document\DocumentList::class)]
class DocumentListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DocumentList();
        $this->mockRepo('DocumentSearchView', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['format' => 'FOO', 'application' => 1]);

        $mockDocument = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['DocumentSearchView']
            ->shouldReceive('fetchList')->once()->with($query, Query::HYDRATE_OBJECT)->andReturn([$mockDocument])
            ->shouldReceive('fetchCount')->once()->with($query)->andReturn(888)
            ->shouldReceive('hasRows')->once()->andReturnUsing(
                function (Qry $query) {
                    static::assertNull($query->getCategory());
                    static::assertEquals([], $query->getDocumentSubCategory());
                    static::assertNull($query->getIsExternal());
                    static::assertNull($query->getShowDocs());
                    static::assertNull($query->getFormat());

                    return true;
                }
            )
            ->shouldReceive('fetchDistinctListExtensions')->once()->andReturn(['RTF', 'DOCX']);

        $this->assertEquals(
            [
                'result' => [['foo' => 'bar']],
                'count' => 888,
                'count-unfiltered' => true,
                'extensionList' => ['RTF', 'DOCX']
            ],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryNoProperLimit(): void
    {
        $query = Qry::create(['format' => 'FOO']);

        $mockDocument = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['DocumentSearchView']
            ->shouldReceive('fetchList')->once()->with($query, Query::HYDRATE_OBJECT)->andReturn([$mockDocument])
            ->shouldReceive('fetchCount')->once()->with($query)->andReturn(888)
            ->shouldReceive('hasRows')->once()->andReturnUsing(
                function (Qry $query) {
                    static::assertNull($query->getCategory());
                    static::assertEquals([], $query->getDocumentSubCategory());
                    static::assertNull($query->getIsExternal());
                    static::assertNull($query->getShowDocs());
                    static::assertNull($query->getFormat());

                    return true;
                }
            );

        $this->assertEquals(
            [
                'result' => [['foo' => 'bar']],
                'count' => 888,
                'count-unfiltered' => true,
                'extensionList' => []
            ],
            $this->sut->handleQuery($query)
        );
    }
}
