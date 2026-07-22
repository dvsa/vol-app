<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\DocumentSubCategoryWithDocs::class)]
final class DocumentSubCategoryWithDocsTest extends AbstractListDataServiceTestCase
{
    /** @var DocumentSubCategoryWithDocs */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new DocumentSubCategoryWithDocs($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        $this->assertEquals(DocumentSubCategoryWithDocs::TYPE_IS_DOC, $this->sut->getCategoryType());
        $this->assertTrue($this->sut->getIsOnlyWithItems());
    }
}
