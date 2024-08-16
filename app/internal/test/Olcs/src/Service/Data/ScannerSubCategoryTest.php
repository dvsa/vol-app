<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\ScannerSubCategory;

/**
 * @covers \Olcs\Service\Data\ScannerSubCategory
 */
class ScannerSubCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var ScannerSubCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ScannerSubCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        static::assertEquals(ScannerSubCategory::TYPE_IS_SCAN, $this->sut->getCategoryType());
    }
}
