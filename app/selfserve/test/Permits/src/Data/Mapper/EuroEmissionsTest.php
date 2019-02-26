<?php

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\Data\Mapper\EuroEmissions;
use Mockery as m;

class EuroEmissionsTest extends MockeryTestCase
{
    public function testMapForFormOptionsEuro5()
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
            'browserTitle' => 'permits.page.euro-emissions.browser.title',
            'question' => 'permits.page.euro6.emissions.question',
            'additionalGuidance' =>
                [
                    0 => 'permits.page.euro6.emissions.guidance.line.1',
                    1 => 'permits.page.euro6.emissions.guidance.line.2',
                ],
        ];

        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('emissions')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValue')
            ->with(true)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setLabel')
            ->with('permits.form.euro5.label')
            ->once()
            ->andReturnSelf();

        $expected = [
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
            'browserTitle' => 'permits.page.euro-emissions.browser.title',
            'question' => 'permits.page.euro5.emissions.question',
            'additionalGuidance' =>
                [
                    0 => 'permits.page.euro5.emissions.guidance.line.1',
                    1 => 'permits.page.euro5.emissions.guidance.line.2',
                ],
        ];

        self::assertEquals($expected, EuroEmissions::mapForFormOptions($inputData, $mockForm));
    }

    public function testMapForFormOptionsEuro6()
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
            'browserTitle' => 'permits.page.euro-emissions.browser.title',
            'question' => 'permits.page.euro6.emissions.question',
            'additionalGuidance' =>
                [
                    0 => 'permits.page.euro6.emissions.guidance.line.1',
                    1 => 'permits.page.euro6.emissions.guidance.line.2',
                ],
        ];

        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('emissions')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValue')
            ->with(true)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setLabel')
            ->with('permits.form.euro6.label')
            ->once()
            ->andReturnSelf();

        $expected = [
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
            'browserTitle' => 'permits.page.euro-emissions.browser.title',
            'question' => 'permits.page.euro6.emissions.question',
            'additionalGuidance' =>
                [
                    0 => 'permits.page.euro6.emissions.guidance.line.1',
                    1 => 'permits.page.euro6.emissions.guidance.line.2',
                ],
        ];

        self::assertEquals($expected, EuroEmissions::mapForFormOptions($inputData, $mockForm));
    }
}
