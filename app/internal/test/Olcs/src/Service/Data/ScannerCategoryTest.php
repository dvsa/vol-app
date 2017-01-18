<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\ScannerCategory;

/**
 * @covers \Olcs\Service\Data\ScannerCategory
 */
class ScannerCategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $sut = new ScannerCategory();

        static::assertEquals(ScannerCategory::TYPE_IS_SCAN, $sut->getCategoryType());
    }
}
