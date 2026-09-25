<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\DocumentAnalysisList;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

final class DocumentAnalysisListTest extends QueryHandlerTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->sut = new DocumentAnalysisList();
        $this->mockRepo('DocumentAnalysis', DocumentAnalysisRepo::class);

        parent::setUp();
    }

    /**
     * The list goes through the repository's standard paged list and count, like DocumentList,
     * so a caller gets one page of rows and the total rather than the whole table.
     */
    public function testHandleQueryReturnsOnePageAndTheCount(): void
    {
        $query = Qry::create([
            'licence' => 7,
            'status' => DocumentAnalysis::STATUS_SUCCESS,
            'page' => 2,
            'limit' => 10,
            'sort' => 'completedAt',
            'order' => 'DESC',
        ]);

        $this->repoMap['DocumentAnalysis']->expects('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            // fetchList hands back the paginator's iterator, not an array.
            ->andReturn(new \ArrayIterator([
                $this->mockAnalysis(1, 11, new \DateTime('2026-03-04 10:11:12')),
                $this->mockAnalysis(2, 12, null),
            ]));
        $this->repoMap['DocumentAnalysis']->expects('fetchCount')
            ->with($query)
            ->andReturn(12);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(12, $result['count']);
        $this->assertCount(2, $result['analyses']);
        $this->assertSame(
            [
                'id' => 1,
                'documentId' => 11,
                'documentDate' => '2026-03-04 10:11:12',
                'status' => DocumentAnalysis::STATUS_SUCCESS,
                'result' => [],
                'metadata' => [],
                'errorDetail' => null,
                'completedAt' => '2026-03-05 09:00:00',
            ],
            $result['analyses'][0]
        );
    }

    /**
     * The document's issued date travels with each analysis so the caller can label tabs
     * without a second, paged document lookup.
     */
    public function testHandleQueryIncludesDocumentDate(): void
    {
        $query = Qry::create(['licence' => 7, 'status' => DocumentAnalysis::STATUS_SUCCESS]);

        $this->repoMap['DocumentAnalysis']->expects('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn(new \ArrayIterator([
                $this->mockAnalysis(1, 11, new \DateTime('2026-03-04 10:11:12')),
                $this->mockAnalysis(2, 12, null),
            ]));
        $this->repoMap['DocumentAnalysis']->expects('fetchCount')->with($query)->andReturn(2);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(11, $result['analyses'][0]['documentId']);
        $this->assertSame('2026-03-04 10:11:12', $result['analyses'][0]['documentDate']);
        // A document without an issued date must not break the list.
        $this->assertNull($result['analyses'][1]['documentDate']);
    }

    private function mockAnalysis(int $id, int $documentId, ?\DateTime $issuedDate): DocumentAnalysis
    {
        $document = m::mock(Document::class);
        $document->allows('getId')->andReturn($documentId);
        $document->allows('getIssuedDate')->with(true)->andReturn($issuedDate);

        $analysis = m::mock(DocumentAnalysis::class);
        $analysis->allows('getId')->andReturn($id);
        $analysis->allows('getDocument')->andReturn($document);
        $analysis->allows('getStatus')->andReturn(DocumentAnalysis::STATUS_SUCCESS);
        $analysis->allows('getResult')->andReturn([]);
        $analysis->allows('getResultMetadata')->andReturn([]);
        $analysis->allows('getErrorDetail')->andReturnNull();
        $analysis->allows('getCompletedAt')->with(true)->andReturn(new \DateTime('2026-03-05 09:00:00'));

        return $analysis;
    }
}
