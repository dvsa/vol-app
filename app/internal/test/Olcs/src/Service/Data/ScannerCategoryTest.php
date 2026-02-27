<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\ScannerCategory;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\ScannerCategory::class)]
class ScannerCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var ScannerCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ScannerCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        static::assertEquals(ScannerCategory::TYPE_IS_SCAN, $this->sut->getCategoryType());
    }
}
