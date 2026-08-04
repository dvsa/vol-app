<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\Radio;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageYesNoRadioValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageYesNoRadioValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class StandardAndCabotageYesNoRadioValidatorTest extends MockeryTestCase
{
    private $yesContentElement;

    private $yesNoRadioValidator;

    #[\Override]
    protected function setUp(): void
    {
        $this->yesContentElement = m::mock(Radio::class);

        $this->yesNoRadioValidator = new StandardAndCabotageYesNoRadioValidator($this->yesContentElement);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidTrue')]
    public function testIsValidTrue($value, $context): void
    {
        $this->assertTrue(
            $this->yesNoRadioValidator->isValid($value, $context)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<(string | null)> | string)>>
     *
     * @psalm-return list{array{value: 'Y', context: array{yesContent: 'yes_content_value'}}, array{value: 'N', context: array{yesContent: null}}}
     */
    public static function dpIsValidTrue(): \Iterator
    {
        yield [
            'value' => 'Y',
            'context' => [
                'yesContent' => 'yes_content_value'
            ]
        ];
        yield [
            'value' => 'N',
            'context' => [
                'yesContent' => null
            ]
        ];
    }

    public function testIsValidFalseSetMessages(): void
    {
        $value = 'Y';
        $context = ['yesContent' => ''];

        $this->yesContentElement->shouldReceive('setMessages')
            ->with(['qanda.bilaterals.standard-and-cabotage.not-selected-message'])
            ->once();

        $this->assertFalse(
            $this->yesNoRadioValidator->isValid($value, $context)
        );
    }
}
