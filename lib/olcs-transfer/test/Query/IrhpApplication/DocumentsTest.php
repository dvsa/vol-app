<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\Documents;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\Documents
 */
class DocumentsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpApplicationId = 30;
        $category = 25;
        $subCategory = 40;

        $sut = Documents::create(
            [
                'id' => $irhpApplicationId,
                'category' => $category,
                'subCategory' => $subCategory,
            ]
        );
        $this->assertEquals($irhpApplicationId, $sut->getId());
        $this->assertEquals($category, $sut->getCategory());
        $this->assertEquals($subCategory, $sut->getSubCategory());
        $this->assertEquals(
            [
                'id' => $irhpApplicationId,
                'category' => $category,
                'subCategory' => $subCategory,
            ],
            $sut->getArrayCopy()
        );
    }
}
