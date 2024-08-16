<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\ScannerCategory;

/**
 * @covers \Olcs\Service\Data\ScannerCategory
 */
class ScannerCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var ScannerCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ScannerCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        static::assertEquals(ScannerCategory::TYPE_IS_SCAN, $this->sut->getCategoryType());
    }
}
