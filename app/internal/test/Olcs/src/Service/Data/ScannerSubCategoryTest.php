<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\ScannerSubCategory;

/**
 * @covers \Olcs\Service\Data\ScannerSubCategory
 */
class ScannerSubCategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $sut = new ScannerSubCategory();

        static::assertEquals(ScannerSubCategory::TYPE_IS_SCAN, $sut->getCategoryType());
    }
}
