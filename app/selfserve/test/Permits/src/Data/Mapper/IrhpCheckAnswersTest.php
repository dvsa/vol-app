<?php

namespace PermitsTest\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Permits\Data\Mapper\IrhpCheckAnswers;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Common\Service\Helper\TranslationHelperService;
use Permits\View\Helper\IrhpApplicationSection;
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
                            'answer' => 'Annual Bilateral (EU and EEA)',
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
                                'OB1234567',
                                'North East of England'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.irhp.application.question.countries',
                            'route' => 'permits/application/countries',
                            'answer' => 'Germany, Croatia',
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.irhp.application.question.no-of-permits',
                            'route' => 'permits/application/no-of-permits',
                            'answer' => [
                                '12 permits for Germany in 2019',
                                '2 permits for Germany in 2020',
                                '6 permits for Croatia in 2020'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                    ],
                    'applicationRef' => 'OB1234567 / 1'
                ],
            ],
            'multilateral' => [
                'data' => [
                    'checkedAnswers' => 1,
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
                            'answer' => 'Annual Multilateral (EU and EEA)',
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
                                'OG4569803',
                                'North East of England'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.irhp.application.question.no-of-permits',
                            'route' => 'permits/application/no-of-permits',
                            'answer' => [
                                '12 permits in 2019',
                                '2 permits in 2020'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                    ],
                    'applicationRef' => 'OG4569803 / 10003'
                ],
            ],
            'ECMT International removals' => [
                'data' => [
                    'checkedAnswers' => 1,
                    'id' => 1,
                    'irhpPermitType' => [
                        'id' => 3,
                        'name' => ['description' => 'ECMT International Removals'],
                    ],
                    'licence' => [
                        'licNo' => 'OG4569803',
                        'trafficArea' => ['name' => 'North East of England'],
                    ],
                    'permitsRequired' => 10,
                    'questionAnswerData' => [
                        [
                            'slug' => 'number-of-permits', //takes the value from permitsRequired
                        ],
                    ],
                    'applicationRef' =>'OG4569803 / 10003',
                    'canCheckAnswers' => 1,
                    'irhpPermitApplications' => [], //not checked for this type but the test requires it :(
                ],
                'expected' => [
                    'canCheckAnswers' => 1,
                    'answers' => [
                        [
                            'question' => 'permits.page.fee.permit.type',
                            'route' => null,
                            'answer' => 'ECMT International Removals',
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
                                'OG4569803',
                                'North East of England'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.irhp.application.question.no-of-permits',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => [
                                '10',
                            ],
                            'questionType' => null,
                            'params' => ['slug' => 'number-of-permits'],
                            'options' => [],
                            'escape' => true,
                        ],
                    ],
                    'applicationRef' => 'OG4569803 / 10003',
                ],
            ],
            'Generic permit type with year for permits required' => [
                'data' => [
                    'checkedAnswers' => 1,
                    'id' => 1,
                    'irhpPermitType' => [
                        'id' => 999,
                        'name' => ['description' => 'Generic permit type'],
                    ],
                    'licence' => [
                        'licNo' => 'OG4569803',
                        'trafficArea' => ['name' => 'North East of England'],
                    ],
                    'irhpPermitApplications' => [
                        [
                            'id' => 7,
                            'irhpPermitWindow' => [
                                'endDate' => '2019-06-29T00:00:00+0000',
                                'id' => 10,
                                'irhpPermitStock' => [
                                    'validTo' => '2019-12-31',
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
                                    'validTo' => '2020-12-31',
                                ]
                            ],
                            'permitsRequired' => 2,
                        ]
                    ],
                    'questionAnswerData' => [
                        [
                            'slug' => 'custom-licence', //ignored
                        ],
                        [
                            'slug' => 'generic.slug.1',
                            'question' => 'generic.question.1',
                            'answer' => 'generic.answer.1',
                            'questionType' => 'generic.questionType.1',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                        ],
                        [
                            'slug' => 'number-of-permits', //will compute from irhpPermitApplications
                        ],
                        [
                            'slug' => 'generic.slug.2',
                            'question' => 'generic.question.2',
                            'answer' => 'generic.answer.2',
                            'questionType' => 'generic.questionType.2',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                        ],
                        [
                            'slug' => 'custom-check-answers', //ignored
                        ],
                        [
                            'slug' => 'custom-declaration', //ignored
                        ],
                    ],
                    'applicationRef' =>'OG4569803 / 10003',
                    'canCheckAnswers' => 1,
                ],
                'expected' => [
                    'canCheckAnswers' => 1,
                    'answers' => [
                        [
                            'question' => 'permits.page.fee.permit.type',
                            'route' => null,
                            'answer' => 'Generic permit type',
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
                                'OG4569803',
                                'North East of England'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'generic.question.1',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => 'generic.answer.1',
                            'questionType' => 'generic.questionType.1',
                            'params' => ['slug' => 'generic.slug.1'],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.irhp.application.question.no-of-permits',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => [
                                '12 permits in 2019',
                                '2 permits in 2020'
                            ],
                            'questionType' => null,
                            'params' => ['slug' => 'number-of-permits'],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'generic.question.2',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => 'generic.answer.2',
                            'questionType' => 'generic.questionType.2',
                            'params' => ['slug' => 'generic.slug.2'],
                            'options' => [],
                            'escape' => true,
                        ],
                    ],
                    'applicationRef' => 'OG4569803 / 10003',
                ],
            ],
            'Generic permit type with year and country for permits required' => [
                'data' => [
                    'checkedAnswers' => 1,
                    'id' => 1,
                    'irhpPermitType' => [
                        'id' => 999,
                        'name' => ['description' => 'Generic permit type'],
                    ],
                    'licence' => [
                        'licNo' => 'OG4569803',
                        'trafficArea' => ['name' => 'North East of England'],
                    ],
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
                                    'validTo' => '2019-12-31',
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
                                    'validTo' => '2020-12-31',
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
                                    'validTo' => '2020-12-31',
                                ]
                            ],
                            'permitsRequired' => 6,
                        ]
                    ],
                    'questionAnswerData' => [
                        [
                            'slug' => 'custom-licence', //ignored
                        ],
                        [
                            'slug' => 'generic.slug.1',
                            'question' => 'generic.question.1',
                            'answer' => 'generic.answer.1',
                            'questionType' => 'generic.questionType.1',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                        ],
                        [
                            'slug' => 'number-of-permits', //will compute from irhpPermitApplications
                        ],
                        [
                            'slug' => 'generic.slug.2',
                            'question' => 'generic.question.2',
                            'answer' => 'generic.answer.2',
                            'questionType' => 'generic.questionType.2',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                        ],
                        [
                            'slug' => 'custom-check-answers', //ignored
                        ],
                        [
                            'slug' => 'custom-declaration', //ignored
                        ],
                    ],
                    'applicationRef' =>'OG4569803 / 10003',
                    'canCheckAnswers' => 1,
                ],
                'expected' => [
                    'canCheckAnswers' => 1,
                    'answers' => [
                        [
                            'question' => 'permits.page.fee.permit.type',
                            'route' => null,
                            'answer' => 'Generic permit type',
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.check-answers.page.question.licence',
                            'route' => 'permits/application/licence',
                            'answer' => [
                                'OG4569803',
                                'North East of England'
                            ],
                            'questionType' => null,
                            'params' => [],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'generic.question.1',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => 'generic.answer.1',
                            'questionType' => 'generic.questionType.1',
                            'params' => ['slug' => 'generic.slug.1'],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'permits.irhp.application.question.no-of-permits',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => [
                                '12 permits for Germany in 2019',
                                '2 permits for Germany in 2020',
                                '6 permits for Croatia in 2020'
                            ],
                            'questionType' => null,
                            'params' => ['slug' => 'number-of-permits'],
                            'options' => [],
                            'escape' => true,
                        ],
                        [
                            'question' => 'generic.question.2',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'answer' => 'generic.answer.2',
                            'questionType' => 'generic.questionType.2',
                            'params' => ['slug' => 'generic.slug.2'],
                            'options' => [],
                            'escape' => true,
                        ],
                    ],
                    'applicationRef' => 'OG4569803 / 10003',
                ],
            ],
        ];
    }
}
