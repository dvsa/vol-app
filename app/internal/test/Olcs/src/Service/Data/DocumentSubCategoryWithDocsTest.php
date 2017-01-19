<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;

/**
 * @covers \Olcs\Service\Data\DocumentSubCategoryWithDocs
 */
class DocumentSubCategoryWithDocsTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $sut = new DocumentSubCategoryWithDocs();

        static::assertEquals(DocumentSubCategoryWithDocs::TYPE_IS_DOC, $sut->getCategoryType());
        static::assertTrue($sut->getIsOnlyWithItems());
    }
}
