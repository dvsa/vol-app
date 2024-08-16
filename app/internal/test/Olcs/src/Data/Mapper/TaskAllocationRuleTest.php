<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TaskAllocationRule as Sut;

class TaskAllocationRuleTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'id' => 1404,
            'version' => 33,
            'category' => 'CAT',
            'subCategory' => 'SUBCAT',
            'goodsOrPsv' => ['id' => 'GOODS'],
            'isMlh' => '',
            'trafficArea' => 'TA',
            'team' => ['id' => 23],
            'user' => 'USER',
        ];
        $expected = [
            'details' => [
                'id' => 1404,
                'version' => 33,
                'category' => 'CAT',
                'subCategory' => 'SUBCAT',
                'goodsOrPsv' => 'GOODS',
                'isMlh' => 'N',
                'trafficArea' => 'TA',
                'teamId' => 23,
                'team' => ['id' => 23],
                'user' => 'USER',
            ]
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromResultAlphaSplit()
    {
        $data = [
            'id' => 1404,
            'version' => 33,
            'category' => 'CAT',
            'subCategory' => 'SUBCAT',
            'goodsOrPsv' => ['id' => 'GOODS'],
            'isMlh' => '',
            'trafficArea' => 'TA',
            'team' => ['id' => 23],
            'user' => null,
            'taskAlphaSplits' => ['XX']
        ];
        $expected = [
            'details' => [
                'id' => 1404,
                'version' => 33,
                'category' => 'CAT',
                'subCategory' => 'SUBCAT',
                'goodsOrPsv' => 'GOODS',
                'isMlh' => 'N',
                'trafficArea' => 'TA',
                'teamId' => 23,
                'team' => ['id' => 23],
                'user' => 'alpha-split',
            ]
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromResultNew()
    {
        $data = [];
        $expected = [];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromForm()
    {
        $data = [
            'details' => [
                'id' => 1404,
                'version' => 33,
                'category' => 'CAT',
                'subCategory' => 'SUBCAT',
                'goodsOrPsv' => 'lcat_gv',
                'isMlh' => 'N',
                'trafficArea' => 'TA',
                'teamId' => 23,
                'team' => 23,
                'user' => 'USER',
            ]
        ];
        $expected = [
            'id' => 1404,
            'version' => 33,
            'category' => 'CAT',
            'subCategory' => 'SUBCAT',
            'goodsOrPsv' => 'lcat_gv',
            'isMlh' => 'N',
            'trafficArea' => 'TA',
            'team' => 23,
            'user' => 'USER',
        ];
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromFormWithTeamId()
    {
        $data = [
            'details' => [
                'id' => 1404,
                'version' => 33,
                'category' => 'CAT',
                'subCategory' => 'SUBCAT',
                'goodsOrPsv' => 'na',
                'isMlh' => 'N',
                'trafficArea' => 'TA',
                'teamId' => 23,
                'team' => null,
                'user' => 'USER',
            ]
        ];
        $expected = [
            'id' => 1404,
            'version' => 33,
            'category' => 'CAT',
            'subCategory' => 'SUBCAT',
            'goodsOrPsv' => null,
            'isMlh' => null,
            'trafficArea' => 'TA',
            'team' => 23,
            'user' => 'USER',
        ];
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromFormWithAlphaSplit()
    {
        $data = [
            'details' => [
                'id' => 1404,
                'version' => 33,
                'category' => 'CAT',
                'subCategory' => 'SUBCAT',
                'goodsOrPsv' => 'lcat_gv',
                'isMlh' => 'N',
                'trafficArea' => 'TA',
                'teamId' => 23,
                'team' => null,
                'user' => 'alpha-split',
            ]
        ];
        $expected = [
            'id' => 1404,
            'version' => 33,
            'category' => 'CAT',
            'subCategory' => 'SUBCAT',
            'goodsOrPsv' => 'lcat_gv',
            'isMlh' => 'N',
            'trafficArea' => 'TA',
            'team' => 23,
        ];
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }
}
