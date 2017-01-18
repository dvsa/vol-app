<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Olcs\Service\Data\DocumentCategoryWithDocs;

/**
 * @covers \Olcs\Service\Data\DocumentCategoryWithDocs
 */
class DocumentCategoryWithDocsTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $sut = new DocumentCategoryWithDocs();

        static::assertEquals(DocumentCategoryWithDocs::TYPE_IS_DOC, $sut->getCategoryType());
        static::assertTrue($sut->getIsOnlyWithItems());
    }
}
