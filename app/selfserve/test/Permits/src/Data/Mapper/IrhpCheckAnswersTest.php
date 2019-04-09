<?php

namespace PermitsTest\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Permits\Data\Mapper\IrhpCheckAnswers;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Common\Service\Helper\TranslationHelperService;
use Zend\Mvc\Controller\Plugin\Url;
use Mockery as m;

class IrhpCheckAnswersTest extends TestCase
{
    public function testMapForDisplayError()
    {
        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('No IRHP answers found');
        $inputData = [];
        IrhpCheckAnswers::mapForDisplay($inputData, $translationHelperService, $url);
    }

    public function testMapForUnsupportedTypeError()
    {
        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This mapper only supports bilateral and multilateral');
        $inputData = [
            'irhpPermitType' => [
                'id' => 'unsupported'
            ],
            'irhpPermitApplications' => [],
        ];
        IrhpCheckAnswers::mapForDisplay($inputData, $translationHelperService, $url);
    }

    /**
     * @dataProvider dpTestMapForDisplay
     */
    public function testMapForDisplay($data, $expected)
    {
        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);

        foreach ($data['irhpPermitApplications'] as $application) {
            if (isset($application['irhpPermitWindow']['irhpPermitStock']['country'])) {
                $translationHelperService
                    ->shouldReceive('translate')
                    ->with($application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc'])
                    ->andReturn($application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc'])
                    ->once();

                $translationHelperService
                    ->shouldReceive('translateReplace')
                    ->with(
                        'permits.check-your-answers.countries',
                        [
                            $application['permitsRequired'],
                            $application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc'],
                            date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']))
                        ]
                    )
                    ->andReturn(
                        $application['permitsRequired'] .
                        ' permits for ' .
                        $application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc'] .
                        ' in '.
                        date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']))
                    );
            } else {
                $translationHelperService
                    ->shouldReceive('translateReplace')
                    ->with(
                        'permits.check-your-answers.no-of-permits',
                        [
                            $application['permitsRequired'],
                            date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']))
                        ]
                    )
                    ->andReturn(
                        $application['permitsRequired'] .
                        ' permits in ' .
                        date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']))
                    );
            }
        }

        self::assertEquals($expected, IrhpCheckAnswers::mapForDisplay($data, $translationHelperService, $url));
    }

    public function dpTestMapForDisplay()
    {
        return [
            'bilateral' => [
                'data' => [
                    'checkedAnswers' => 1,
                    'declaration' =>1,
                    'id' => 1,
                    'irhpPermitType' =>
                        [
                            'id' => 4,
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
                    'canCheckAnswers' => 1
                ],
                'expected' => [
                    'canCheckAnswers' => 1,
                    'answers' => [
                        [
                            'question' => 'permits.page.fee.permit.type',
                            'route' => null,
                            'answer' => 'Annual Bilateral (EU and EEA)'
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
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
                    ],

                    'applicationRef' => 'OB1234567 / 1'
                ]
            ],
            'multilateral' => [
                'data' => [
                    'checkedAnswers' => 1,
                    'declaration' =>1,
                    'id' => 1,
                    'irhpPermitType' =>
                        [
                            'id' => 5,
                            'name' => ['description' => 'Annual Multilateral (EU and EEA)']
                        ],
                    'licence' =>
                        [
                            'licNo' => 'OG4569803',
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
                                    'validTo' => '2020-12-31'
                                ]
                            ],
                            'permitsRequired' => 2,
                        ]
                    ],
                    'applicationRef' =>'OG4569803 / 10003',
                    'canCheckAnswers' => 1
                ],
                'expected' => [
                    'canCheckAnswers' => 1,
                    'answers' => [
                        [
                            'question' => 'permits.page.fee.permit.type',
                            'route' => null,
                            'answer' => 'Annual Multilateral (EU and EEA)'
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
                                'OG4569803',
                                'North East of England'
                            ]
                        ],
                        [
                            'question' => 'permits.irhp.application.question.no-of-permits',
                            'route' => 'permits/application/no-of-permits',
                            'answer' => [
                                '12 permits in 2019',
                                '2 permits in 2020'
                            ]
                        ],
                    ],

                    'applicationRef' => 'OG4569803 / 10003'
                ]
            ]
        ];
    }
}
