<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\ScannerSubCategory;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\ScannerSubCategory::class)]
class ScannerSubCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var ScannerSubCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ScannerSubCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        static::assertEquals(ScannerSubCategory::TYPE_IS_SCAN, $this->sut->getCategoryType());
    }
}
