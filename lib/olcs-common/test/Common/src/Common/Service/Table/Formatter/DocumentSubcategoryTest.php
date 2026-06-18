<?php

/**
 * Document subcategory formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

/**
 * Document subcategory formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentSubcategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group DocumentSubcategoryFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\DocumentSubcategory())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                [
                    'documentSubCategoryName' => 'foo',
                    'isExternal' => false,
                    'ciId' => null
                ],
                'foo'
            ],
            [
                [
                    'documentSubCategoryName' => 'foo',
                    'isExternal' => true,
                    'ciId' => null
                ],
                'foo (selfserve)'
            ],
            [
                [
                    'documentSubCategoryName' => 'foo',
                    'isExternal' => true,
                    'ciId' => 123
                ],
                'foo (selfserve) (emailed)'
            ]
        ];
    }
}
