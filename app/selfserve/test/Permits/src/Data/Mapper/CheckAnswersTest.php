<?php

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\Data\Mapper\CheckAnswers;

class CheckAnswersTest extends MockeryTestCase
{
    public function testMapForDisplayEuro5()
    {
        $inputData = [
            'application' => [
                'cabotage' => true,
                'checkedAnswers' => false,
                'countrys' => [],
                'declaration' => false,
                'emissions' => true,
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
                'permitsRequired' => 5,
                'sectors' => [
                    'name' => 'Mail and parcels',
                ],
                'trips' => 43,
                'applicationRef' => 'OG4563323 / 4',
                'canCheckAnswers' => true,
                'hasCheckedAnswers' => false,
                'isNotYetSubmitted' => true,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5
            ],
            'windows' => [
                'windows' => [
                    0 => [
                        'endDate' => '2019-12-01T00:00:00+0000',
                        'id' => 1,
                        'startDate' => '2018-10-01T00:00:00+0000',
                        'emissionsCategory' => [
                            'description' => 'Euro 5',
                            'id' => 'emissions_cat_euro5',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                0 => [
                    'question' => 'permits.check-answers.page.question.licence',
                    'route' => 'permits/licence',
                    'answer' => [
                        0 => 'OG4563323',
                        1 => 'North East of England',
                    ],
                ],
                1 => [
                    'question' => 'permits.form.euro5.label',
                    'route' => 'permits/ecmt-emissions',
                    'answer' => 'Yes',
                ],
                2 => [
                    'question' => 'permits.form.cabotage.label',
                    'route' => 'permits/ecmt-cabotage',
                    'answer' => 'Yes',
                ],
                3 => [
                    'question' => 'permits.form.restricted.countries.euro5.label',
                    'route' => 'permits/ecmt-countries',
                    'answer' => 'Yes',
                ],
                4 => [
                    'question' => 'permits.page.permits.required.question',
                    'route' => 'permits/ecmt-no-of-permits',
                    'answer' => 5,
                ],
                5 => [
                    'question' => 'permits.page.number-of-trips.question',
                    'route' => 'permits/ecmt-trips',
                    'answer' => 43,
                ],
                6 => [
                    'question' => 'permits.page.international.journey.question',
                    'route' => 'permits/ecmt-international-journey',
                    'answer' => 'More than 90%',
                ],
                7 => [
                    'question' => 'permits.page.sectors.question',
                    'route' => 'permits/ecmt-sectors',
                    'answer' => 'Mail and parcels',
                ],
            ],
            'applicationRef' => 'OG4563323 / 4'
        ];

        self::assertEquals($expected, CheckAnswers::mapForDisplay($inputData));
    }

    public function testMapForDisplayEuro6()
    {
        $inputData = [
            'application' => [
                'cabotage' => true,
                'checkedAnswers' => false,
                'countrys' => [],
                'declaration' => false,
                'emissions' => true,
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
                'permitsRequired' => 5,
                'sectors' => [
                    'name' => 'Mail and parcels',
                ],
                'trips' => 43,
                'applicationRef' => 'OG4563323 / 4',
                'canCheckAnswers' => true,
                'hasCheckedAnswers' => false,
                'isNotYetSubmitted' => true,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6
            ],
            'windows' => [
                'windows' => [
                    0 => [
                        'endDate' => '2019-12-01T00:00:00+0000',
                        'id' => 1,
                        'startDate' => '2018-10-01T00:00:00+0000',
                        'emissionsCategory' => [
                            'description' => 'Euro 6',
                            'id' => 'emissions_cat_euro6',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                0 => [
                    'question' => 'permits.check-answers.page.question.licence',
                    'route' => 'permits/licence',
                    'answer' => [
                        0 => 'OG4563323',
                        1 => 'North East of England',
                    ],
                ],
                1 => [
                    'question' => 'permits.form.euro6.label',
                    'route' => 'permits/ecmt-emissions',
                    'answer' => 'Yes',
                ],
                2 => [
                    'question' => 'permits.form.cabotage.label',
                    'route' => 'permits/ecmt-cabotage',
                    'answer' => 'Yes',
                ],
                3 => [
                    'question' => 'permits.page.restricted-countries.question',
                    'route' => 'permits/ecmt-countries',
                    'answer' => 'No',
                ],
                4 => [
                    'question' => 'permits.page.permits.required.question',
                    'route' => 'permits/ecmt-no-of-permits',
                    'answer' => 5,
                ],
                5 => [
                    'question' => 'permits.page.number-of-trips.question',
                    'route' => 'permits/ecmt-trips',
                    'answer' => 43,
                ],
                6 => [
                    'question' => 'permits.page.international.journey.question',
                    'route' => 'permits/ecmt-international-journey',
                    'answer' => 'More than 90%',
                ],
                7 => [
                    'question' => 'permits.page.sectors.question',
                    'route' => 'permits/ecmt-sectors',
                    'answer' => 'Mail and parcels',
                ],
            ],
            'applicationRef' => 'OG4563323 / 4'
        ];

        self::assertEquals($expected, CheckAnswers::mapForDisplay($inputData));
    }

    public function testMapForDisplayEuro6Countries()
    {
        $inputData = [
            'application' => [
                'cabotage' => true,
                'checkedAnswers' => false,
                'countrys' => [['id' => 'AT', 'countryDesc' => 'Austria']],
                'declaration' => false,
                'emissions' => true,
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
                'permitsRequired' => 5,
                'sectors' => [
                    'name' => 'Mail and parcels',
                ],
                'trips' => 43,
                'applicationRef' => 'OG4563323 / 4',
                'canCheckAnswers' => true,
                'hasCheckedAnswers' => false,
                'isNotYetSubmitted' => true,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6
            ],
            'windows' => [
                'windows' => [
                    0 => [
                        'endDate' => '2019-12-01T00:00:00+0000',
                        'id' => 1,
                        'startDate' => '2018-10-01T00:00:00+0000',
                        'emissionsCategory' => [
                            'description' => 'Euro 6',
                            'id' => 'emissions_cat_euro6',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                0 => [
                    'question' => 'permits.check-answers.page.question.licence',
                    'route' => 'permits/licence',
                    'answer' => [
                        0 => 'OG4563323',
                        1 => 'North East of England',
                    ],
                ],
                1 => [
                    'question' => 'permits.form.euro6.label',
                    'route' => 'permits/ecmt-emissions',
                    'answer' => 'Yes',
                ],
                2 => [
                    'question' => 'permits.form.cabotage.label',
                    'route' => 'permits/ecmt-cabotage',
                    'answer' => 'Yes',
                ],
                3 => [
                    'question' => 'permits.page.restricted-countries.question',
                    'route' => 'permits/ecmt-countries',
                    'answer' => ['Yes', 'Austria'],
                ],
                4 => [
                    'question' => 'permits.page.permits.required.question',
                    'route' => 'permits/ecmt-no-of-permits',
                    'answer' => 5,
                ],
                5 => [
                    'question' => 'permits.page.number-of-trips.question',
                    'route' => 'permits/ecmt-trips',
                    'answer' => 43,
                ],
                6 => [
                    'question' => 'permits.page.international.journey.question',
                    'route' => 'permits/ecmt-international-journey',
                    'answer' => 'More than 90%',
                ],
                7 => [
                    'question' => 'permits.page.sectors.question',
                    'route' => 'permits/ecmt-sectors',
                    'answer' => 'Mail and parcels',
                ],
            ],
            'applicationRef' => 'OG4563323 / 4'
        ];

        self::assertEquals($expected, CheckAnswers::mapForDisplay($inputData));
    }
}
