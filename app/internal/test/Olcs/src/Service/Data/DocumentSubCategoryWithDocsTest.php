<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\DocumentSubCategoryWithDocs::class)]
class DocumentSubCategoryWithDocsTest extends AbstractListDataServiceTestCase
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
        static::assertEquals(DocumentSubCategoryWithDocs::TYPE_IS_DOC, $this->sut->getCategoryType());
        static::assertTrue($this->sut->getIsOnlyWithItems());
    }
}
