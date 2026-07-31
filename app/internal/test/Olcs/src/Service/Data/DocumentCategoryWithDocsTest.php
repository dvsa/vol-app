<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\DocumentCategoryWithDocs;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\DocumentCategoryWithDocs::class)]
final class DocumentCategoryWithDocsTest extends AbstractListDataServiceTestCase
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
        $this->assertEquals(DocumentCategoryWithDocs::TYPE_IS_DOC, $this->sut->getCategoryType());
        $this->assertTrue($this->sut->getIsOnlyWithItems());
    }
}
