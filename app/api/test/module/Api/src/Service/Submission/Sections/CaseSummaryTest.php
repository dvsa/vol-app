<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\CaseSummary;

/**
 * Class CaseSummaryTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
final class CaseSummaryTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = CaseSummary::class;

    protected const string LICENCE_START_DATE = '2012-01-01 15:00:00';

    protected const array BASE_EXPECTED_RESULT = [
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

        // Each case is built inside a closure so its mocks are created when the test runs (inside the
        // test's own Mockery container) rather than at suite build time, where providers execute.

        /* existing pre lgv licence only */

        $preLgvLicenceCase = static function () {
            $case = static::getCase();
            $case->setApplication(null);
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthVehicles', 'totAuthTrailers']);

            return $case;
        };

        /* mixed fleet with lgv licence only */

        $mixedFleetLicenceCase = static function () {
            $case = static::getCase();
            $case->setApplication(null);
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthHgvVehicles', 'totAuthLgvVehicles', 'totAuthTrailers']);
            $case->getLicence()->updateTotAuthHgvVehicles(7);
            $case->getLicence()->updateTotAuthLgvVehicles(3);

            return $case;
        };

        /* lgv only licence only */

        $lgvOnlyLicenceCase = static function () {
            $case = static::getCase();
            $case->setApplication(null);
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthLgvVehicles']);
            $case->getLicence()->updateTotAuthLgvVehicles(4);

            return $case;
        };

        /* existing pre lgv application */

        $preLgvApplicationCase = static function () {
            $case = static::getCase();
            $case->getApplication()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthVehicles', 'totAuthTrailers']);
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->never();

            return $case;
        };

        /* mixed fleet with lgv application */

        $mixedFleetApplicationCase = static function () {
            $case = static::getCase();
            $case->getApplication()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthHgvVehicles', 'totAuthLgvVehicles', 'totAuthTrailers']);
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->never();
            $case->getLicence()->updateTotAuthHgvVehicles(7);
            $case->getLicence()->updateTotAuthLgvVehicles(3);

            return $case;
        };

        /* lgv only application */

        $lgvOnlyApplicationCase = static function () {
            $case = static::getCase();
            $case->getApplication()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthLgvVehicles']);
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->never();
            $case->getLicence()->updateTotAuthLgvVehicles(4);

            return $case;
        };

        /* existing pre lgv variation */

        $preLgvVariationCase = static function () {
            $case = static::getCase();
            $case->getApplication()->setIsVariation(true);
            $case->getApplication()->shouldReceive('getApplicableAuthProperties')
                ->never();
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthVehicles', 'totAuthTrailers']);

            return $case;
        };

        /* mixed fleet with lgv variation */

        $mixedFleetVariationCase = static function () {
            $case = static::getCase();
            $case->getApplication()->setIsVariation(true);
            $case->getApplication()->shouldReceive('getApplicableAuthProperties')
                ->never();
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthHgvVehicles', 'totAuthLgvVehicles', 'totAuthTrailers']);
            $case->getLicence()->updateTotAuthHgvVehicles(7);
            $case->getLicence()->updateTotAuthLgvVehicles(3);

            return $case;
        };

        /* lgv only variation */

        $lgvOnlyVariationCase = static function () {
            $case = static::getCase();
            $case->getApplication()->setIsVariation(true);
            $case->getApplication()->shouldReceive('getApplicableAuthProperties')
                ->never();
            $case->getLicence()->shouldReceive('getApplicableAuthProperties')
                ->withNoArgs()
                ->andReturn(['totAuthLgvVehicles']);
            $case->getLicence()->updateTotAuthLgvVehicles(4);

            return $case;
        };

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

    #[\Override]
    public static function getCase(): mixed
    {
        $case = parent::getCase();

        $case->getLicence()->setInForceDate(static::LICENCE_START_DATE);

        return $case;
    }
}
