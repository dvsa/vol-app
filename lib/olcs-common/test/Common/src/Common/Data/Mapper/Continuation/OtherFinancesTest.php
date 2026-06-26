<?php

namespace CommonTest\Data\Mapper\Continuation;

use Common\Data\Mapper\Continuation\OtherFinances;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Data\Mapper\Continuation\OtherFinances
 */
class OtherFinancesTest extends MockeryTestCase
{
    /**
     * @var OtherFinances
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new OtherFinances();
    }

    public function testMapFromResult(): void
    {
        $data = [
            'version' => 99,
            'hasOtherFinances' => 'Y',
            'otherFinancesAmount' => '345.67',
            'otherFinancesDetails' => 'FOO',
        ];

        $expected = [
            'version' => 99,
            'finances' => [
                'yesNo' => 'Y',
                'yesContent' => [
                    'amount' => '345.67',
                    'detail' => 'FOO'
                ]
            ]
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function testMapFromResultDefaults(): void
    {
        $data = [
            'version' => 99,
            'hasOtherFinances' => 'N',
            'otherFinancesAmount' => '345.67',
            'otherFinancesDetails' => 'FOO',
        ];

        $expected = [
            'version' => 99,
            'finances' => [
                'yesNo' => 'N',
                'yesContent' => [
                    'amount' => '345.67',
                    'detail' => 'FOO'
                ]
            ]
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function testMapFromForm(): void
    {
        $formData = [
            'version' => 99,
            'finances' => [
                'yesNo' => 'Y',
                'yesContent' => [
                    'amount' => '2776',
                    'detail' => 'FOO'
                ]
            ]
        ];

        $expected = [
            'version' => 99,
            'hasOtherFinances' => 'Y',
            'otherFinancesAmount' => '2776',
            'otherFinancesDetails' => 'FOO',
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($formData));
    }

    public function testMapFromFormNo(): void
    {
        $formData = [
            'version' => 99,
            'finances' => [
                'yesNo' => 'N',
                'yesContent' => [
                    'amount' => '2776',
                    'detail' => 'FOO'
                ]
            ]
        ];

        $expected = [
            'version' => 99,
            'hasOtherFinances' => 'N',
            'otherFinancesAmount' => null,
            'otherFinancesDetails' => null,
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($formData));
    }
}
