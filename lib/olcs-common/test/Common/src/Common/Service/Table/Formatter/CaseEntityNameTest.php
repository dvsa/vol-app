<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CaseEntityName;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Table\Formatter\CaseEntityName::class)]
final class CaseEntityNameTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFormat')]
    public function testFormat($data, $expect): void
    {
        $sut = new CaseEntityName();
        $this->assertSame($expect, $sut->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<(array<(array<(array<string> | string)> | string | null)> | string)>> | string)>>
     *
     * @psalm-return array{'not-dta': array{data: array{caseType: array{id: 'case_t_tm'}, transportManager: array{homeCd: array{person: null}}}, expect: ''}, tm: array{data: array{caseType: array{id: 'case_t_tm'}, transportManager: array{homeCd: array{person: array{title: array{description: 'unit_TitleDesc'}, forename: 'unit_ForeN', familyName: 'unit_FamilyN'}}}}, expect: 'unit_TitleDesc unit_ForeN unit_FamilyN'}, 'lic|app': array{data: array{caseType: array{id: 'case_t_lic'}, licence: array{organisation: array{name: 'unit_Org'}}}, expect: 'unit_Org'}}
     */
    public static function dpTestFormat(): \Iterator
    {
        yield 'not-dta' => [
            'data' => [
                'caseType' => [
                    'id' => \Common\RefData::CASE_TYPE_TM,
                ],
                'transportManager' => [
                    'homeCd' => [
                        'person' => null,
                    ],
                ],
            ],
            'expect' => '',
        ];
        yield 'tm' => [
            'data' => [
                'caseType' => [
                    'id' => \Common\RefData::CASE_TYPE_TM,
                ],
                'transportManager' => [
                    'homeCd' => [
                        'person' => [
                            'title' => [
                                'description' => 'unit_TitleDesc',
                            ],
                            'forename' => 'unit_ForeN',
                            'familyName' => 'unit_FamilyN',
                        ],
                    ],
                ],
            ],
            'expect' => 'unit_TitleDesc unit_ForeN unit_FamilyN',
        ];
        yield 'lic|app' => [
            'data' => [
                'caseType' => [
                    'id' => \Common\RefData::CASE_TYPE_LICENCE,
                ],
                'licence' => [
                    'organisation' => [
                        'name' => 'unit_Org',
                    ],
                ],
            ],
            'expect' => 'unit_Org',
        ];
    }
}
