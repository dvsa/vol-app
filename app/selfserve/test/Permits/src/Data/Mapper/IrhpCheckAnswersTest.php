<?php

namespace PermitsTest\Data\Mapper;

use Mockery as m;
use Permits\Data\Mapper\IrhpCheckAnswers;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class IrhpCheckAnswersTest extends TestCase
{

    public function testMapForDisplay()
    {
        $inputData = [
            'checkedAnswers' => 1,
            'declaration' =>1,
            'id' => 1,
            'irhpPermitType' =>
                [
                    'name' => ['description' => 'Annual Bilateral (EU and EEA)']
                ],
            'licence' =>
                [
                    'licNo' => 'OB1234567',
                    'trafficArea' => [ 'name' => 'North East of England' ]
                ],
            'status' => ['description' => 'Not Yet Submitted'],
            'irhpPermitApplications' => [],
            'applicationRef' =>'OB1234567 / 1',
            'canCheckAnswers' => 1,
            'permitsRequired' => 12,
        ];

        $outputData['canCheckAnswers'] = 1;
        $outputData['answers'] = [
            0 => [
                'question' => 'permits.page.fee.permit.type',
                'route' => null,
                'answer' => 'Annual Bilateral (EU and EEA)'
            ],
            1 => [
                'question' => 'permits.check-answers.page.question.licence',
                'route' => 'permits/application/licence',
                'answer' =>[
                    0 => 'OB1234567',
                    1 => 'North East of England'
                ]
            ],
            2 => [
                'question' => 'permits.irhp.application.question.countries',
                'route' => 'permits/application/countries',
                'answer' => ''
            ],
            3 => [
                'question' => 'permits.irhp.application.question.no-of-permits',
                'route' => 'permits/application/no-of-permits',
                'answer' => []
            ],
        ];
        $outputData['applicationRef'] = $inputData['applicationRef'];

        self::assertEquals($outputData, IrhpCheckAnswers::mapForDisplay($inputData));
    }
}
