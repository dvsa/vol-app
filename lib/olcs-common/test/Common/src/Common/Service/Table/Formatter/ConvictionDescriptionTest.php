<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\ConvictionDescription;

/**
 * Class ConvictionDescriptionTest
 * @package CommonTest\Service\Table\Formatter
 */
final class ConvictionDescriptionTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('convictionDataProvider')]
    public function testFormat($convictionData, $expected): void
    {
        $sut = new ConvictionDescription();
        $result = $sut->format(
            $convictionData,
            []
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<string> | string | null)> | string)>>
     *
     * @psalm-return list{list{array{convictionCategory: array{id: 'conv_c_cat_1144', description: 'User defined'}, categoryText: 'userdefinedtext_01234567890123456789'}, 'userdefinedtext_01234567890123...'}, list{array{convictionCategory: null, categoryText: 'userdefinedtext_01234567890123456789'}, 'userdefinedtext_01234567890123...'}, list{array{convictionCategory: array{id: 'conv_c_cat_someother', description: 'Some Other_012345678901234567890123456789'}, categoryText: 'userdefinedtext_01234567890123456789'}, 'Some Other_0123456789012345678...'}}
     */
    public static function convictionDataProvider(): \Iterator
    {
        yield [
            /* Test User defined category - should use categoryText */
            [
                'convictionCategory' => [
                    'id' => 'conv_c_cat_1144',
                    'description' => 'User defined',
                ],
                'categoryText' => 'userdefinedtext_01234567890123456789'
            ],
            'userdefinedtext_01234567890123...'
        ];
        yield [
            /* Test empty category - should use categoryText */
            [
                'convictionCategory' => null,
                'categoryText' => 'userdefinedtext_01234567890123456789'
            ],
            'userdefinedtext_01234567890123...'
        ];
        yield [
            /* Test other category - should use categoryDescription */
            [
                'convictionCategory' => [
                    'id' => 'conv_c_cat_someother',
                    'description' => 'Some Other_012345678901234567890123456789',
                ],
                'categoryText' => 'userdefinedtext_01234567890123456789'
            ],
            'Some Other_0123456789012345678...'
        ];
    }
}
