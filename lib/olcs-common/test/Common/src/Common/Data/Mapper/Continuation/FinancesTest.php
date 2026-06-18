<?php

namespace CommonTest\Data\Mapper\Continuation;

use Common\Data\Mapper\Continuation\Finances;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Data\Mapper\Continuation\Finances
 */
class FinancesTest extends MockeryTestCase
{
    /**
     * @var Finances
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Finances();
    }

    public function testMapFromResult(): void
    {
        $data = [
            'version' => 99,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'Y',
            'overdraftAmount' => '234.56',
            'hasOtherFinances' => 'N',
            'otherFinancesAmount' => '345.67',
            'otherFinancesDetails' => 'FOO',
            'hasFactoring' => 'Y',
            'factoringAmount' => '2776',
        ];

        $expected = [
            'finances' => [
                'version' => 99,
                'averageBalance' => '123.45',
                'overdraftFacility' => [
                    'yesNo' => 'Y',
                    'yesContent' => '234.56'
                ],
                'factoring' => [
                    'yesNo' => 'Y',
                    'yesContent' => [
                        'amount' => '2776'
                    ]
                ]
            ]
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function testMapFromResultDefaults(): void
    {
        $data = [
            'version' => 99,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'Y',
            'hasOtherFinances' => 'N',
            'hasFactoring' => 'N',
        ];

        $expected = [
            'finances' => [
                'version' => 99,
                'averageBalance' => '123.45',
                'overdraftFacility' => [
                    'yesNo' => 'Y',
                    'yesContent' => ''
                ],
                'factoring' => [
                    'yesNo' => 'N',
                    'yesContent' => [
                        'amount' => ''
                    ]
                ]
            ]
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function testMapFromForm(): void
    {
        $formData = [
            'finances' => [
                'version' => 99,
                'averageBalance' => '123.45',
                'overdraftFacility' => [
                    'yesNo' => 'Y',
                    'yesContent' => '234.56'
                ],
                'factoring' => [
                    'yesNo' => 'Y',
                    'yesContent' => [
                        'amount' => '2607',
                    ]
                ]
            ]
        ];

        $expected = [
            'version' => 99,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'Y',
            'overdraftAmount' => '234.56',
            'hasFactoring' => 'Y',
            'factoringAmount' => '2607',
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($formData));
    }

    public function testMapFromFormNo(): void
    {
        $formData = [
            'finances' => [
                'version' => 99,
                'averageBalance' => '123.45',
                'overdraftFacility' => [
                    'yesNo' => 'N',
                    'yesContent' => '234.56'
                ],
                'factoring' => [
                    'yesNo' => 'N',
                    'yesContent' => [
                        'amount' => '2607',
                    ]
                ]
            ]
        ];

        $expected = [
            'version' => 99,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'N',
            'overdraftAmount' => null,
            'hasFactoring' => 'N',
            'factoringAmount' => null,
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($formData));
    }
}
