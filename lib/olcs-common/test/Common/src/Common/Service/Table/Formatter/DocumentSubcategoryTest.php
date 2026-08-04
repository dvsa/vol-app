<?php

/**
 * Document subcategory formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

/**
 * Document subcategory formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class DocumentSubcategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('DocumentSubcategoryFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new \Common\Service\Table\Formatter\DocumentSubcategory()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [
            [
                'documentSubCategoryName' => 'foo',
                'isExternal' => false,
                'ciId' => null
            ],
            'foo'
        ];
        yield [
            [
                'documentSubCategoryName' => 'foo',
                'isExternal' => true,
                'ciId' => null
            ],
            'foo (selfserve)'
        ];
        yield [
            [
                'documentSubCategoryName' => 'foo',
                'isExternal' => true,
                'ciId' => 123
            ],
            'foo (selfserve) (emailed)'
        ];
    }
}
