<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

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
     * The document's issued date travels with each analysis so the caller can label tabs
     * without a second, paged document lookup.
     */
    public function testHandleQueryIncludesDocumentDate(): void
    {
        $query = Qry::create(['licence' => 7, 'status' => DocumentAnalysis::STATUS_SUCCESS]);

        $this->repoMap['DocumentAnalysis']->expects('fetchAnalyses')
            ->with($query)
            ->andReturn([
                $this->mockAnalysis(1, 11, new \DateTime('2026-03-04 10:11:12')),
                $this->mockAnalysis(2, 12, null),
            ]);

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
