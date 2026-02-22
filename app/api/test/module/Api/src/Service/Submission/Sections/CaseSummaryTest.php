<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\CaseSummary;

/**
 * Class CaseSummaryTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class CaseSummaryTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = CaseSummary::class;

    protected const LICENCE_START_DATE = '2012-01-01 15:00:00';

    protected const BASE_EXPECTED_RESULT = [
        'id' => 99,
        'caseType' => 'case type 1',
        'ecmsNo' => 'ecms1234',
        'organisationName' => 'Org name',
        'isMlh' => false,
        'organisationType' => 'org_type-desc',
        'businessType' => 'nob1',
        'licNo' => 'OB12345',
        'licenceStartDate' => '01/01/2012',
        'licenceType' => 'lic_type-desc',
        'goodsOrPsv' => 'goods-desc',
        'licenceStatus' => 'lic_status-desc',
        'vehiclesInPossession' => 3,
        'serviceStandardDate' => '',
        'disqualificationStatus' => 'None',
    ];

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $preLgvExpectedResult = [
            'data' => [
                'overview' => array_merge(
                    static::BASE_EXPECTED_RESULT,
                    [
                        'totAuthorisedVehicles' => null,
                        'totAuthorisedTrailers' => 5,
                        'trailersInPossession' => 5
                    ]
                )
            ]
        ];

        $mixedFleetExpectedResult = [
            'data' => [
                'overview' => array_merge(
                    static::BASE_EXPECTED_RESULT,
                    [
                        'totAuthorisedHgvVehicles' => 7,
                        'totAuthorisedLgvVehicles' => 3,
                        'totAuthorisedTrailers' => 5,
                        'trailersInPossession' => 5
                    ]
                )
            ]
        ];

        $lgvOnlyExpectedResult = [
            'data' => [
                'overview' => array_merge(
                    static::BASE_EXPECTED_RESULT,
                    [
                        'totAuthorisedLgvVehicles' => 4
                     ]
                )
            ]
        ];

        /* existing pre lgv licence only */

        $preLgvLicenceCase = static::getCase();
        $preLgvLicenceCase->setApplication(null);
        $preLgvLicenceCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthVehicles', 'totAuthTrailers']);

        /* mixed fleet with lgv licence only */

        $mixedFleetLicenceCase = static::getCase();
        $mixedFleetLicenceCase->setApplication(null);
        $mixedFleetLicenceCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthHgvVehicles', 'totAuthLgvVehicles', 'totAuthTrailers']);
        $mixedFleetLicenceCase->getLicence()->updateTotAuthHgvVehicles(7);
        $mixedFleetLicenceCase->getLicence()->updateTotAuthLgvVehicles(3);

        /* lgv only licence only */

        $lgvOnlyLicenceCase = static::getCase();
        $lgvOnlyLicenceCase->setApplication(null);
        $lgvOnlyLicenceCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthLgvVehicles']);
        $lgvOnlyLicenceCase->getLicence()->updateTotAuthLgvVehicles(4);

        /* existing pre lgv application */

        $preLgvApplicationCase = static::getCase();
        $preLgvApplicationCase->getApplication()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthVehicles', 'totAuthTrailers']);
        $preLgvApplicationCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->never();

        /* mixed fleet with lgv application */

        $mixedFleetApplicationCase = static::getCase();
        $mixedFleetApplicationCase->getApplication()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthHgvVehicles', 'totAuthLgvVehicles', 'totAuthTrailers']);
        $mixedFleetApplicationCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->never();
        $mixedFleetApplicationCase->getLicence()->updateTotAuthHgvVehicles(7);
        $mixedFleetApplicationCase->getLicence()->updateTotAuthLgvVehicles(3);

        /* lgv only application */

        $lgvOnlyApplicationCase = static::getCase();
        $lgvOnlyApplicationCase->getApplication()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthLgvVehicles']);
        $lgvOnlyApplicationCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->never();
        $lgvOnlyApplicationCase->getLicence()->updateTotAuthLgvVehicles(4);

        /* existing pre lgv variation */

        $preLgvVariationCase = static::getCase();
        $preLgvVariationCase->getApplication()->setIsVariation(true);
        $preLgvVariationCase->getApplication()->shouldReceive('getApplicableAuthProperties')
            ->never();
        $preLgvVariationCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthVehicles', 'totAuthTrailers']);

        /* mixed fleet with lgv variation */

        $mixedFleetVariationCase = static::getCase();
        $mixedFleetVariationCase->getApplication()->setIsVariation(true);
        $mixedFleetVariationCase->getApplication()->shouldReceive('getApplicableAuthProperties')
            ->never();
        $mixedFleetVariationCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthHgvVehicles', 'totAuthLgvVehicles', 'totAuthTrailers']);
        $mixedFleetVariationCase->getLicence()->updateTotAuthHgvVehicles(7);
        $mixedFleetVariationCase->getLicence()->updateTotAuthLgvVehicles(3);

        /* lgv only variation */

        $lgvOnlyVariationCase = static::getCase();
        $lgvOnlyVariationCase->getApplication()->setIsVariation(true);
        $lgvOnlyVariationCase->getApplication()->shouldReceive('getApplicableAuthProperties')
            ->never();
        $lgvOnlyVariationCase->getLicence()->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn(['totAuthLgvVehicles']);
        $lgvOnlyVariationCase->getLicence()->updateTotAuthLgvVehicles(4);

        return [
            [$preLgvLicenceCase, $preLgvExpectedResult],
            [$mixedFleetLicenceCase, $mixedFleetExpectedResult],
            [$lgvOnlyLicenceCase, $lgvOnlyExpectedResult],
            [$preLgvApplicationCase, $preLgvExpectedResult],
            [$mixedFleetApplicationCase, $mixedFleetExpectedResult],
            [$lgvOnlyApplicationCase, $lgvOnlyExpectedResult],
            [$preLgvVariationCase, $preLgvExpectedResult],
            [$mixedFleetVariationCase, $mixedFleetExpectedResult],
            [$lgvOnlyVariationCase, $lgvOnlyExpectedResult],
         ];
    }

    public static function getCase(): mixed
    {
        $case = parent::getCase();

        $case->getLicence()->setInForceDate(static::LICENCE_START_DATE);

        return $case;
    }
}
