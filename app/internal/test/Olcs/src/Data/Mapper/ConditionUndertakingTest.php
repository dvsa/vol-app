<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\ConditionUndertaking as Sut;
use Laminas\Form\FormInterface;

/**
 * ConditionUndertaking Mapper Test
 */
class ConditionUndertakingTest extends MockeryTestCase
{
    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public static function mapFromResultDataProvider(): array
    {
        return [
            // add condition attached to OC 24
            [
                [
                    'case' => 24,
                    'fulfilled' => 'Y',
                    'conditionType' => 'cdt_cond',
                    'attachedTo' => ['id' => 'cat_oc'],
                    'operatingCentre' => ['id' => 24]
                ],
                [
                    'fields' => [
                        'case' => 24,
                        'fulfilled' => 'Y',
                        'type' => 'cdt_cond',
                        'attachedTo' => 24,
                        'conditionType' => 'cdt_cond',
                        'operatingCentre' => 24
                    ]
                ]
            ],
            // edit undertaking attached to licence 7
            [
                [
                    'id' => 44,
                    'version' => 6,
                    'case' => 24,
                    'fulfilled' => 'Y',
                    'conditionType' => 'cdt_und',
                    'attachedTo' => ['id' => 'cat_lic']
                ],
                [
                    'fields' => [
                        'id' => 44,
                        'version' => 6,
                        'case' => 24,
                        'fulfilled' => 'Y',
                        'type' => 'cdt_und',
                        'attachedTo' => 'cat_lic',
                        'conditionType' => 'cdt_und'
                    ]
                ]
            ],
        ];
    }

    public function testMapFromForm(): void
    {
        $inData = [
            'fields' => [
                'id' => 99,
                'version' => 3,
                'case' => 24,
                'fulfilled' => 'Y',
                'conditionType' => 'cdt_cond',
                'attachedTo' => 24,
            ]
        ];
        $expected = [
            'id' => 99,
            'version' => 3,
            'case' => 24,
            'fulfilled' => 'Y',
            'conditionType' => 'cdt_cond',
            'attachedTo' => 'cat_oc',
            'operatingCentre' => 24
        ];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
