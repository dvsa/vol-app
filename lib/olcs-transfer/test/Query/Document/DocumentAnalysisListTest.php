<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(DocumentAnalysisList::class)]
final class DocumentAnalysisListTest extends MockeryTestCase
{
    public function testGetSet(): void
    {
        $sut = DocumentAnalysisList::create([
            'application' => 'unit_application',
            'licence' => 'unit_licence',
            'document' => 'unit_document',
            'status' => 'unit_status',
            'page' => 'unit_page',
            'limit' => 'unit_limit',
            'sort' => 'unit_sort',
            'order' => 'unit_order',
        ]);

        $this->assertEquals('unit_application', $sut->getApplication());
        $this->assertEquals('unit_licence', $sut->getLicence());
        $this->assertEquals('unit_document', $sut->getDocument());
        $this->assertEquals('unit_status', $sut->getStatus());
        $this->assertEquals('unit_page', $sut->getPage());
        $this->assertEquals('unit_limit', $sut->getLimit());
        $this->assertEquals('unit_sort', $sut->getSort());
        $this->assertEquals('unit_order', $sut->getOrder());
    }

    /**
     * The repository only pages and orders queries that declare the interfaces, so the list
     * stays bounded by a page limit rather than by which entity scope a caller happened to pass.
     */
    public function testIsPagedAndOrdered(): void
    {
        $sut = DocumentAnalysisList::create([]);

        $this->assertInstanceOf(PagedQueryInterface::class, $sut);
        $this->assertInstanceOf(OrderedQueryInterface::class, $sut);
    }
}
