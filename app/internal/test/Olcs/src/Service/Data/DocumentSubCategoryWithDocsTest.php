<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;

/**
 * @covers \Olcs\Service\Data\DocumentSubCategoryWithDocs
 */
class DocumentSubCategoryWithDocsTest extends AbstractListDataServiceTestCase
{
    /** @var DocumentSubCategoryWithDocs */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new DocumentSubCategoryWithDocs($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        static::assertEquals(DocumentSubCategoryWithDocs::TYPE_IS_DOC, $this->sut->getCategoryType());
        static::assertTrue($this->sut->getIsOnlyWithItems());
    }
}
