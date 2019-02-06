<?php

namespace PermitsTest\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Permits\Data\Mapper\IrhpCheckAnswers;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class IrhpCheckAnswersTest extends TestCase
{
    public function testMapForDisplayError()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('No IRHP answers found');
        $inputData = [];
        IrhpCheckAnswers::mapForDisplay($inputData);
    }

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
            'irhpPermitApplications' => [
                [
                    'id' => 7,
                    'irhpPermitWindow' => [
                        'endDate' => '2019-06-29T00:00:00+0000',
                        'id' => 10,
                        'irhpPermitStock' => [
                            'country' => [
                                'countryDesc' => 'Germany'
                            ],
                            'validTo' => '2019-12-31'
                        ]
                    ],
                    'permitsRequired' => 12,
                ],
                [
                    'id' => 8,
                    'irhpPermitWindow' => [
                        'endDate' => '2019-06-29T00:00:00+0000',
                        'id' => 11,
                        'irhpPermitStock' => [
                            'country' => [
                                'countryDesc' => 'Germany'
                            ],
                            'validTo' => '2020-12-31'
                        ]
                    ],
                    'permitsRequired' => 2,
                ],
                [
                    'id' => 9,
                    'irhpPermitWindow' => [
                        'endDate' => '2019-06-29T00:00:00+0000',
                        'id' => 11,
                        'irhpPermitStock' => [
                            'country' => [
                                'countryDesc' => 'Croatia'
                            ],
                            'validTo' => '2020-12-31'
                        ]
                    ],
                    'permitsRequired' => 6,
                ]
            ],
            'applicationRef' =>'OB1234567 / 1',
            'canCheckAnswers' => 1,
        ];

        $outputData['canCheckAnswers'] = 1;
        $outputData['answers'] = [
            [
                'question' => 'permits.page.fee.permit.type',
                'route' => null,
                'answer' => 'Annual Bilateral (EU and EEA)'
            ],
            [
                'question' => 'permits.check-answers.page.question.licence',
                'route' => 'permits/application/licence',
                'answer' =>[
                    'OB1234567',
                    'North East of England'
                ]
            ],
            [
                'question' => 'permits.irhp.application.question.countries',
                'route' => 'permits/application/countries',
                'answer' => 'Germany, Croatia'
            ],
            [
                'question' => 'permits.irhp.application.question.no-of-permits',
                'route' => 'permits/application/no-of-permits',
                'answer' => [
                    '12 permits for Germany in 2019',
                    '2 permits for Germany in 2020',
                    '6 permits for Croatia in 2020'
                ]
            ],
        ];
        $outputData['applicationRef'] = $inputData['applicationRef'];

        self::assertEquals($outputData, IrhpCheckAnswers::mapForDisplay($inputData));
    }
}
