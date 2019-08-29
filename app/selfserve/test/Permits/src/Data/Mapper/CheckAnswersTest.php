<?php

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\Data\Mapper\CheckAnswers;
use Permits\Data\Mapper\EcmtNoOfPermits;
use Permits\Data\Mapper\IrhpCheckAnswers;
use Permits\View\Helper\EcmtSection;

class CheckAnswersTest extends MockeryTestCase
{
    private $translator;

    private $ecmtNoOfPermits;

    private $irhpCheckAnswers;

    private $checkAnswers;

    public function setUp()
    {
        $this->applicationReference = 'OG4563323 / 4';
        $this->permitTypeDescription = 'Annual ECMT';

        $this->licenceData = [
            'licNo' => 'OG4563323',
            'trafficArea' => [
                'name' => 'North East of England',
            ],
        ];

        $this->cabotage = 1;
        $this->roadworthiness = 1;
        $this->emissions = 1;
        $this->trips = 43;
        $this->internationalJourneysDescription = 'More than 90%';
        $this->sectorName = 'Mail and parcels';

        $this->permitTypeAnswer = [
            'question' => 'permits.page.fee.permit.type',
            'route' => null,
            'answer' => 'Annual ECMT',
            'questionType' => null,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->licenceAnswer = [
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
        ];

        $this->cabotageAnswer = [
            'question' => 'permits.form.cabotage.label',
            'route' => EcmtSection::ROUTE_ECMT_CABOTAGE,
            'answer' => 1,
            'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->roadworthinessAnswer = [
            'question' => 'permits.page.roadworthiness.question',
            'route' => EcmtSection::ROUTE_ECMT_ROADWORTHINESS,
            'answer' => 1,
            'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->euroEmissionsAnswer = [
            'question' => 'permits.form.euro-emissions.label',
            'route' => EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
            'answer' => 1,
            'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->permitsRequiredAnswer = [
            'question' => 'permits.page.permits.required.question',
            'route' => EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
            'answer' => [
                '<strong>Permits for 2029</strong>',
                'no of permits line 1',
                'no of permits line 2'
            ],
            'questionType' => RefData::QUESTION_TYPE_STRING,
            'params' => [],
            'options' => [],
            'escape' => false,
        ];

        $this->numberOfTripsAnswer = [
            'question' => 'permits.page.number-of-trips.question',
            'route' => EcmtSection::ROUTE_ECMT_TRIPS,
            'answer' => 43,
            'questionType' => RefData::QUESTION_TYPE_INTEGER,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->internationalJourneysAnswer = [
            'question' => 'permits.page.international.journey.question',
            'route' => EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
            'answer' => 'More than 90%',
            'questionType' => RefData::QUESTION_TYPE_STRING,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->sectorsAnswer = [
            'question' => 'permits.page.sectors.question',
            'route' => EcmtSection::ROUTE_ECMT_SECTORS,
            'answer' => 'Mail and parcels',
            'questionType' => RefData::QUESTION_TYPE_STRING,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->translator = m::mock(TranslationHelperService::class);

        $this->translator->shouldReceive('translateReplace')
            ->with('permits.check-your-answers.no-of-permits.year', [2029])
            ->once()
            ->andReturn('Permits for 2029');

        $this->ecmtNoOfPermits = m::mock(EcmtNoOfPermits::class);

        $this->irhpCheckAnswers = m::mock(IrhpCheckAnswers::class);

        $this->irhpCheckAnswers->shouldReceive('permitTypeAnswer')
            ->with($this->permitTypeDescription)
            ->andReturn($this->permitTypeAnswer);

        $this->irhpCheckAnswers->shouldReceive('licenceAnswer')
            ->with($this->licenceData, EcmtSection::ROUTE_LICENCE)
            ->andReturn($this->licenceAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.form.cabotage.label',
                $this->cabotage,
                EcmtSection::ROUTE_ECMT_CABOTAGE,
                RefData::QUESTION_TYPE_BOOLEAN
            )
            ->andReturn($this->cabotageAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.roadworthiness.question',
                $this->roadworthiness,
                EcmtSection::ROUTE_ECMT_ROADWORTHINESS,
                RefData::QUESTION_TYPE_BOOLEAN
            )
            ->andReturn($this->roadworthinessAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.form.euro-emissions.label',
                $this->emissions,
                EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
                RefData::QUESTION_TYPE_BOOLEAN
            )
            ->andReturn($this->euroEmissionsAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.permits.required.question',
                [
                    '<strong>Permits for 2029</strong>',
                    'no of permits line 1',
                    'no of permits line 2'
                ],
                EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
                RefData::QUESTION_TYPE_STRING,
                [],
                [],
                false
            )
            ->andReturn($this->permitsRequiredAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.number-of-trips.question',
                $this->trips,
                EcmtSection::ROUTE_ECMT_TRIPS,
                RefData::QUESTION_TYPE_INTEGER
            )
            ->andReturn($this->numberOfTripsAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.international.journey.question',
                $this->internationalJourneysDescription,
                EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
                RefData::QUESTION_TYPE_STRING
            )
            ->andReturn($this->internationalJourneysAnswer);

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.sectors.question',
                $this->sectorName,
                EcmtSection::ROUTE_ECMT_SECTORS,
                RefData::QUESTION_TYPE_STRING
            )
            ->andReturn($this->sectorsAnswer);

        $this->checkAnswers = new CheckAnswers(
            $this->translator,
            $this->ecmtNoOfPermits,
            $this->irhpCheckAnswers
        );
    }

    public function testMapForDisplay()
    {
        $application = [
            'cabotage' => $this->cabotage,
            'checkedAnswers' => false,
            'countrys' => [],
            'declaration' => false,
            'emissions' => $this->emissions,
            'roadworthiness' => $this->roadworthiness,
            'hasRestrictedCountries' => false,
            'internationalJourneys' => [
                'description' => $this->internationalJourneysDescription,
            ],
            'licence' => $this->licenceData,
            'permitType' => [
                'description' => $this->permitTypeDescription,
                'id' => 'permit_ecmt',
            ],
            'requiredEuro5' => 4,
            'requiredEuro6' => 7,
            'sectors' => [
                'name' => $this->sectorName,
            ],
            'trips' => $this->trips,
            'applicationRef' => $this->applicationReference,
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
        ];

        $inputData = [
            'application' => $application
        ];

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($application)
            ->andReturn(['no of permits line 1', 'no of permits line 2']);

        $restrictedCountriesAnswer = [
            'question' => 'permits.page.restricted-countries.question',
            'route' => EcmtSection::ROUTE_ECMT_COUNTRIES,
            'answer' => 'No',
            'questionType' => RefData::QUESTION_TYPE_STRING,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.restricted-countries.question',
                'No',
                EcmtSection::ROUTE_ECMT_COUNTRIES,
                RefData::QUESTION_TYPE_STRING
            )
            ->andReturn($restrictedCountriesAnswer);

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                $this->permitTypeAnswer,
                $this->licenceAnswer,
                $this->cabotageAnswer,
                $this->roadworthinessAnswer,
                $restrictedCountriesAnswer,
                $this->euroEmissionsAnswer,
                $this->permitsRequiredAnswer,
                $this->numberOfTripsAnswer,
                $this->internationalJourneysAnswer,
                $this->sectorsAnswer
            ],
            'applicationRef' => $this->applicationReference
        ];

        $this->assertEquals(
            $expected,
            $this->checkAnswers->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayWithCountries()
    {
        $application = [
            'cabotage' => $this->cabotage,
            'checkedAnswers' => false,
            'countrys' => [['id' => 'AT', 'countryDesc' => 'Austria']],
            'declaration' => false,
            'emissions' => $this->emissions,
            'roadworthiness' => $this->roadworthiness,
            'hasRestrictedCountries' => true,
            'internationalJourneys' => [
                'description' => $this->internationalJourneysDescription,
            ],
            'licence' => $this->licenceData,
            'permitType' => [
                'description' => $this->permitTypeDescription,
                'id' => 'permit_ecmt',
            ],
            'requiredEuro5' => 4,
            'requiredEuro6' => 7,
            'sectors' => [
                'name' => $this->sectorName,
            ],
            'trips' => $this->trips,
            'applicationRef' => $this->applicationReference,
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
        ];

        $inputData = [
            'application' => $application
        ];

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($application)
            ->andReturn(['no of permits line 1', 'no of permits line 2']);

        $restrictedCountriesAnswer = [
            'question' => 'permits.page.restricted-countries.question',
            'route' => EcmtSection::ROUTE_ECMT_COUNTRIES,
            'answer' => ['Yes', 'Austria'],
            'questionType' => RefData::QUESTION_TYPE_STRING,
            'params' => [],
            'options' => [],
            'escape' => true,
        ];

        $this->irhpCheckAnswers->shouldReceive('answer')
            ->with(
                'permits.page.restricted-countries.question',
                ['Yes', 'Austria'],
                EcmtSection::ROUTE_ECMT_COUNTRIES,
                RefData::QUESTION_TYPE_STRING
            )
            ->andReturn($restrictedCountriesAnswer);

        $expected = [
            'canCheckAnswers' => true,
            'answers' => [
                $this->permitTypeAnswer,
                $this->licenceAnswer,
                $this->cabotageAnswer,
                $this->roadworthinessAnswer,
                $restrictedCountriesAnswer,
                $this->euroEmissionsAnswer,
                $this->permitsRequiredAnswer,
                $this->numberOfTripsAnswer,
                $this->internationalJourneysAnswer,
                $this->sectorsAnswer,
            ],
            'applicationRef' => $this->applicationReference
        ];

        $this->assertEquals(
            $expected,
            $this->checkAnswers->mapForDisplay($inputData)
        );
    }
}
