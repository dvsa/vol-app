<?php

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\Data\Mapper\CheckAnswers;
use Permits\View\Helper\EcmtSection;
use Zend\Mvc\Controller\Plugin\Url;

class CheckAnswersTest extends MockeryTestCase
{
    /** @var TranslationHelperService|m\MockInterface */
    private $translator;

    /** @var Url|m\MockInterface */
    private $url;

    public function setUp()
    {
        $this->url = m::mock(Url::class);
        $this->translator = m::mock(TranslationHelperService::class);

        $this->translator->shouldReceive('translateReplace')
            ->with('permits.check-your-answers.no-of-permits.year', [2029])
            ->once()
            ->andReturn('Permits for 2029');
        $this->translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->once()
            ->andReturn('Euro 5 minimum emission standard');
        $this->translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->once()
            ->andReturn('Euro 6 minimum emission standard');
        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [4, 'Euro 5 minimum emission standard']
            )
            ->once()
            ->andReturn('4 permits for Euro 5 minimum emission standard');
        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [7, 'Euro 6 minimum emission standard']
            )
            ->once()
            ->andReturn('7 permits for Euro 6 minimum emission standard');
    }

    public function testMapForDisplay()
    {
        $inputData = [
            'application' => [
                'cabotage' => 1,
                'checkedAnswers' => false,
                'countrys' => [],
                'declaration' => false,
                'emissions' => 1,
                'roadworthiness' => 1,
                'hasRestrictedCountries' => false,
                'internationalJourneys' => [
                    'description' => 'More than 90%',
                ],
                'licence' => [
                    'licNo' => 'OG4563323',
                    'trafficArea' => [
                        'name' => 'North East of England',
                    ],
                ],
                'permitType' => [
                    'description' => 'Annual ECMT',
                    'id' => 'permit_ecmt',
                ],
                'requiredEuro5' => 4,
                'requiredEuro6' => 7,
                'sectors' => [
                    'name' => 'Mail and parcels',
                ],
                'trips' => 43,
                'applicationRef' => 'OG4563323 / 4',
                'canCheckAnswers' => true,
                'hasCheckedAnswers' => false,
                'isNotYetSubmitted' => true,
                'irhpPermitApplications' => [
                    0 => [
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validTo' => '2029-12-25'
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                [
                    'question' => 'permits.page.fee.permit.type',
                    'route' => null,
                    'answer' => 'Annual ECMT',
                    'questionType' => null,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.check-answers.page.question.licence',
                    'route' => EcmtSection::ROUTE_LICENCE,
                    'answer' => [
                        0 => 'OG4563323',
                        1 => 'North East of England',
                    ],
                    'questionType' => null,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.form.cabotage.label',
                    'route' => EcmtSection::ROUTE_ECMT_CABOTAGE,
                    'answer' => 1,
                    'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.roadworthiness.question',
                    'route' => EcmtSection::ROUTE_ECMT_ROADWORTHINESS,
                    'answer' => 1,
                    'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.restricted-countries.question',
                    'route' => EcmtSection::ROUTE_ECMT_COUNTRIES,
                    'answer' => 'No',
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.form.euro-emissions.label',
                    'route' => EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
                    'answer' => 1,
                    'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.permits.required.question',
                    'route' => EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
                    'answer' => [
                        '<strong>Permits for 2029</strong>',
                        '4 permits for Euro 5 minimum emission standard',
                        '7 permits for Euro 6 minimum emission standard'
                    ],
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => false,
                ],
                [
                    'question' => 'permits.page.number-of-trips.question',
                    'route' => EcmtSection::ROUTE_ECMT_TRIPS,
                    'answer' => 43,
                    'questionType' => RefData::QUESTION_TYPE_INTEGER,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.international.journey.question',
                    'route' => EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
                    'answer' => 'More than 90%',
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.sectors.question',
                    'route' => EcmtSection::ROUTE_ECMT_SECTORS,
                    'answer' => 'Mail and parcels',
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
            ],
            'applicationRef' => 'OG4563323 / 4'
        ];

        self::assertEquals($expected, CheckAnswers::mapForDisplay($inputData, $this->translator, $this->url));
    }

    public function testMapForDisplayWithCountries()
    {
        $inputData = [
            'application' => [
                'cabotage' => 1,
                'checkedAnswers' => false,
                'countrys' => [['id' => 'AT', 'countryDesc' => 'Austria']],
                'declaration' => false,
                'emissions' => 1,
                'roadworthiness' => 1,
                'hasRestrictedCountries' => true,
                'internationalJourneys' => [
                    'description' => 'More than 90%',
                ],
                'licence' => [
                    'licNo' => 'OG4563323',
                    'trafficArea' => [
                        'name' => 'North East of England',
                    ],
                ],
                'permitType' => [
                    'description' => 'Annual ECMT',
                    'id' => 'permit_ecmt',
                ],
                'requiredEuro5' => 4,
                'requiredEuro6' => 7,
                'sectors' => [
                    'name' => 'Mail and parcels',
                ],
                'trips' => 43,
                'applicationRef' => 'OG4563323 / 4',
                'canCheckAnswers' => true,
                'hasCheckedAnswers' => false,
                'isNotYetSubmitted' => true,
                'irhpPermitApplications' => [
                    0 => [
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validTo' => '2029-12-25'
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                [
                    'question' => 'permits.page.fee.permit.type',
                    'route' => null,
                    'answer' => 'Annual ECMT',
                    'questionType' => null,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.check-answers.page.question.licence',
                    'route' => EcmtSection::ROUTE_LICENCE,
                    'answer' => [
                        0 => 'OG4563323',
                        1 => 'North East of England',
                    ],
                    'questionType' => null,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.form.cabotage.label',
                    'route' => EcmtSection::ROUTE_ECMT_CABOTAGE,
                    'answer' => 1,
                    'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.roadworthiness.question',
                    'route' => 'permits/ecmt-roadworthiness',
                    'answer' => 1,
                    'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.restricted-countries.question',
                    'route' => EcmtSection::ROUTE_ECMT_COUNTRIES,
                    'answer' => ['Yes', 'Austria'],
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.form.euro-emissions.label',
                    'route' => EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
                    'answer' => 1,
                    'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.permits.required.question',
                    'route' => EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
                    'answer' => [
                        '<strong>Permits for 2029</strong>',
                        '4 permits for Euro 5 minimum emission standard',
                        '7 permits for Euro 6 minimum emission standard'
                    ],
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => false,
                ],
                [
                    'question' => 'permits.page.number-of-trips.question',
                    'route' => EcmtSection::ROUTE_ECMT_TRIPS,
                    'answer' => 43,
                    'questionType' => RefData::QUESTION_TYPE_INTEGER,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.international.journey.question',
                    'route' => EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
                    'answer' => 'More than 90%',
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
                [
                    'question' => 'permits.page.sectors.question',
                    'route' => EcmtSection::ROUTE_ECMT_SECTORS,
                    'answer' => 'Mail and parcels',
                    'questionType' => RefData::QUESTION_TYPE_STRING,
                    'params' => [],
                    'options' => [],
                    'escape' => true,
                ],
            ],
            'applicationRef' => 'OG4563323 / 4'
        ];

        self::assertEquals($expected, CheckAnswers::mapForDisplay($inputData, $this->translator, $this->url));
    }
}
