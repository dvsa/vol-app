<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\DocumentCategoryWithDocs;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\DocumentCategoryWithDocs::class)]
class DocumentCategoryWithDocsTest extends AbstractListDataServiceTestCase
{
    /** @var DocumentCategoryWithDocs */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new DocumentCategoryWithDocs($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        static::assertEquals(DocumentCategoryWithDocs::TYPE_IS_DOC, $this->sut->getCategoryType());
        static::assertTrue($this->sut->getIsOnlyWithItems());
    }
}
