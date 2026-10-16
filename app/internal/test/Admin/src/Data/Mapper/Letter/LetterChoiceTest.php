<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper\Letter;

use Admin\Data\Mapper\Letter\LetterChoice;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see LetterChoice
 */
final class LetterChoiceTest extends MockeryTestCase
{
    public function testGoodsOrPsvRoundTrips(): void
    {
        $formData = LetterChoice::mapFromResult([
            'id' => 1,
            'choiceKey' => 'time-limited-interims',
            'label' => 'Interims',
            'goodsOrPsv' => ['id' => 'lcat_gv', 'description' => 'Goods Vehicle'],
        ]);

        $this->assertSame('lcat_gv', $formData['letterChoice']['goodsOrPsv']);
        $this->assertSame('lcat_gv', LetterChoice::mapFromForm($formData)['goodsOrPsv']);
    }

    public function testNoGoodsOrPsvShowsAsBoth(): void
    {
        $formData = LetterChoice::mapFromResult(['id' => 1, 'goodsOrPsv' => null]);

        $this->assertNull($formData['letterChoice']['goodsOrPsv']);
    }

    public function testBothIsSentAsNoGoodsOrPsv(): void
    {
        // The "Both" option posts '', which the API reads as clear it
        $commandData = LetterChoice::mapFromForm(['letterChoice' => ['id' => 1, 'goodsOrPsv' => '']]);

        $this->assertArrayNotHasKey('goodsOrPsv', $commandData);
    }
}
