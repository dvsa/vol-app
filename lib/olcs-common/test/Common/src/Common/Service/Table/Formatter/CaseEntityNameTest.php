<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CaseEntityName;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Table\Formatter\CaseEntityName
 */
class CaseEntityNameTest extends MockeryTestCase
{
    /**
     * @dataProvider  dpTestFormat
     */
    public function testFormat($data, $expect): void
    {
        $sut = new CaseEntityName();
        static::assertSame($expect, $sut->format($data));
    }

    /**
     * @return ((((string|string[])[]|null|string)[]|string)[][]|string)[][]
     *
     * @psalm-return array{'not-dta': array{data: array{caseType: array{id: 'case_t_tm'}, transportManager: array{homeCd: array{person: null}}}, expect: ''}, tm: array{data: array{caseType: array{id: 'case_t_tm'}, transportManager: array{homeCd: array{person: array{title: array{description: 'unit_TitleDesc'}, forename: 'unit_ForeN', familyName: 'unit_FamilyN'}}}}, expect: 'unit_TitleDesc unit_ForeN unit_FamilyN'}, 'lic|app': array{data: array{caseType: array{id: 'case_t_lic'}, licence: array{organisation: array{name: 'unit_Org'}}}, expect: 'unit_Org'}}
     */
    public function dpTestFormat(): array
    {
        return [
            'not-dta' => [
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
            ],
            'tm' => [
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
            ],
            'lic|app' => [
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
            ],
        ];
    }
}
