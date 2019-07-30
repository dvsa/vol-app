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

    /**
     * @dataProvider dpTestMapForDisplayShortTerm
     */
    public function testMapForDisplayShortTerm($requiredEuro5, $requiredEuro6, $year, $expected)
    {
        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);

        $irhpPermitApplications = [
            0 => [
                'requiredEuro5' => $requiredEuro5,
                'requiredEuro6' => $requiredEuro6,
                'irhpPermitWindow' => [
                    'irhpPermitStock' => [
                        'validTo' => $year . '-12-31',
                    ]
                ]
            ],
        ];

        $input = $this->genericInput($irhpPermitApplications);
        $output = $this->genericOutput('st-number-of-permits', $expected, false);

        $translationHelperService->shouldReceive('translateReplace')
            ->once()
            ->with(
                'permits.check-your-answers.no-of-permits.year',
                [$year]
            )
            ->andReturn('year heading');
        $translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->times($requiredEuro5 ? 1 : 0)
            ->andReturn('Euro 5 minimum emission standard');
        $translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->times($requiredEuro6 ? 1 : 0)
            ->andReturn('Euro 6 minimum emission standard');
        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [$requiredEuro5, 'Euro 5 minimum emission standard']
            )
            ->times($requiredEuro5 ? 1 : 0)
            ->andReturn($requiredEuro5 . ' permits for Euro 5 minimum emission standard');
        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [$requiredEuro6, 'Euro 6 minimum emission standard']
            )
            ->times($requiredEuro6 ? 1 : 0)
            ->andReturn($requiredEuro6 . ' permits for Euro 6 minimum emission standard');

        self::assertEquals($output, IrhpCheckAnswers::mapForDisplay($input, $translationHelperService, $url));
    }

    public function dpTestMapForDisplayShortTerm()
    {
        $requiredEuro5 = 3;
        $requiredEuro6 = 7;

        $yearHeading = '<strong>year heading</strong>';
        $euro5Text = $requiredEuro5 . ' permits for Euro 5 minimum emission standard';
        $euro6Text = $requiredEuro6 . ' permits for Euro 6 minimum emission standard';

        return [
            [
                null,
                $requiredEuro6,
                2029,
                [
                    $yearHeading,
                    $euro6Text,
                ],
            ],
            [
                0,
                $requiredEuro6,
                2029,
                [
                    $yearHeading,
                    $euro6Text,
                ],
            ],
            [
                $requiredEuro5,
                null,
                2030,
                [
                    $yearHeading,
                    $euro5Text,
                ],
            ],
            [
                $requiredEuro5,
                null,
                2030,
                [
                    $yearHeading,
                    $euro5Text,
                ],
            ],
            [
                $requiredEuro5,
                $requiredEuro6,
                2030,
                [
                    $yearHeading,
                    $euro5Text,
                    $euro6Text,
                ],
            ],
        ];
    }

    private function genericInput($irhpPermitApplications)
    {
        return [
            'irhpPermitType' => [
                'id' => 2,
                'name' => ['description' => 'ECMT Short Term'],
            ],
            'licence' => [
                'licNo' => 'OB1234567',
                'trafficArea' => ['name' => 'North East of England'],
            ],
            'irhpPermitApplications' => $irhpPermitApplications,
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
                    'slug' => 'st-number-of-permits', //will compute from irhpPermitApplications
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
            'applicationRef' =>'OB1234567 / 10003',
            'canCheckAnswers' => 1,
        ];
    }

    private function genericOutput($numPermitsSlug, $expectedNumPermits, $escapeNumPermits)
    {
        return [
            'canCheckAnswers' => 1,
            'answers' => [
                [
                    'question' => 'permits.page.fee.permit.type',
                    'route' => null,
                    'answer' => 'ECMT Short Term',
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
                    'answer' => $expectedNumPermits,
                    'questionType' => null,
                    'params' => ['slug' => $numPermitsSlug],
                    'options' => [],
                    'escape' => $escapeNumPermits,
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
            'applicationRef' => 'OB1234567 / 10003',
        ];
    }
}
