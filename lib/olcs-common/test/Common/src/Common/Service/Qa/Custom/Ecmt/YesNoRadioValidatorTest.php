<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\RestrictedCountriesMultiCheckbox;
use Common\Service\Qa\Custom\Ecmt\YesNoRadioValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * YesNoRadioValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class YesNoRadioValidatorTest extends MockeryTestCase
{
    private $yesContentElement;

    private $yesNoRadioValidator;

    #[\Override]
    protected function setUp(): void
    {
        $this->yesContentElement = m::mock(RestrictedCountriesMultiCheckbox::class);

        $this->yesNoRadioValidator = new YesNoRadioValidator($this->yesContentElement);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidTrue')]
    public function testIsValidTrue($value, $context): void
    {
        $this->assertTrue(
            $this->yesNoRadioValidator->isValid($value, $context)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<string> | string)> | string)>>
     *
     * @psalm-return list{array{value: 'Y', context: array{yesContent: list{'RU', 'IT'}}}, array{value: 'N', context: array{yesContent: ''}}}
     */
    public static function dpIsValidTrue(): \Iterator
    {
        yield [
            'value' => 'Y',
            'context' => [
                'yesContent' => ['RU', 'IT']
            ]
        ];
        yield [
            'value' => 'N',
            'context' => [
                'yesContent' => ''
            ]
        ];
    }

    public function testIsValidFalseSetMessages(): void
    {
        $value = 'Y';
        $context = ['yesContent' => ''];

        $this->yesContentElement->shouldReceive('setMessages')
            ->with(['qanda.ecmt.restricted-countries.error.select-countries'])
            ->once();

        $this->assertFalse(
            $this->yesNoRadioValidator->isValid($value, $context)
        );
    }
}
