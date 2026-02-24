<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Submission\Sections\OperatingCentres::class)]
class OperatingCentresTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\OperatingCentres::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getApplicationCase();

        $case2 = static::getApplicationCase();
        $oc = $case2->getLicence()->getOperatingCentres();
        $oc->current()->getOperatingCentre()->setAddress(null);

        //  --  prepare data for 'add, update, delete oc in application'
        $case3 = static::getApplicationCase();
        $app = $case3->getApplication();
        $appOcRels = $case3->getApplication()->getOperatingCentres();

        //  add operation center
        $ocNew = static::generateOperatingCentre(3);
        $ocNew->getAddress()->setPostcode('A_first');

        $appOcRels->add(
            (new ApplicationOperatingCentre($app, $ocNew))
                ->setAction(ApplicationOperatingCentre::ACTION_ADD)
                ->setNoOfVehiclesRequired(903)
                ->setNoOfTrailersRequired(803)
        );

        //  update operation center in licence
        $ocUpd = static::generateOperatingCentre(2);
        $ocUpd->getAddress()->setPostcode('Z_last');

        $appOcRels->add(
            (new ApplicationOperatingCentre($app, $ocUpd))
                ->setAction(ApplicationOperatingCentre::ACTION_UPDATE)
                ->setNoOfVehiclesRequired(902)
                ->setNoOfTrailersRequired(802)
        );

        //  delete operation center in licence
        $appOcRels->add(
            (new ApplicationOperatingCentre($app, static::generateOperatingCentre(1)))
                ->setAction(ApplicationOperatingCentre::ACTION_DELETE)
        );

        //  --  prepare data for 'no oper centres'
        $case4 = static::getApplicationCase();
        $case4->getLicence()->getOperatingCentres()->clear();

        return [
            [
                $case,
                [
                    'data' => [
                        'tables' => [
                            'operating-centres' => [
                                0 => [
                                    'id' => 1,
                                    'version' => 1,
                                    'totAuthVehicles' => 6,
                                    'totAuthTrailers' => 4,
                                    'OcAddress' => [
                                        'addressLine1' => '1_a1',
                                        'addressLine2' => '1_a2',
                                        'addressLine3' => '1_a3',
                                        'addressLine4' => null,
                                        'town' => '1t',
                                        'postcode' => 'pc11PC',
                                        'countryCode' => null,
                                    ],
                                ],
                                [
                                    'id' => 2,
                                    'version' => 2,
                                    'totAuthVehicles' => 6,
                                    'totAuthTrailers' => 4,
                                    'OcAddress' => [
                                        'addressLine1' => '2_a1',
                                        'addressLine2' => '2_a2',
                                        'addressLine3' => '2_a3',
                                        'addressLine4' => null,
                                        'town' => '2t',
                                        'postcode' => 'pc21PC',
                                        'countryCode' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                $case2,
                [
                    'data' => [
                        'tables' => [
                            'operating-centres' => [
                                [
                                    'id' => 1,
                                    'version' => 1,
                                    'totAuthVehicles' => 6,
                                    'totAuthTrailers' => 4,
                                    'OcAddress' => [],
                                ],
                                [
                                    'id' => 2,
                                    'version' => 2,
                                    'totAuthVehicles' => 6,
                                    'totAuthTrailers' => 4,
                                    'OcAddress' => [
                                        'addressLine1' => '2_a1',
                                        'addressLine2' => '2_a2',
                                        'addressLine3' => '2_a3',
                                        'addressLine4' => null,
                                        'town' => '2t',
                                        'postcode' => 'pc21PC',
                                        'countryCode' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'add, update, delete oc in application' => [
                $case3,
                [
                    'data' => [
                        'tables' => [
                            'operating-centres' => [
                                [
                                    'id' => 3,
                                    'version' => 3,
                                    'totAuthVehicles' => 903,
                                    'totAuthTrailers' => 803,
                                    'OcAddress' => [
                                        'addressLine1' => '3_a1',
                                        'addressLine2' => '3_a2',
                                        'addressLine3' => '3_a3',
                                        'addressLine4' => null,
                                        'town' => '3t',
                                        'postcode' => 'A_first',
                                        'countryCode' => null,
                                    ],
                                ],
                                [
                                    'id' => 2,
                                    'version' => 2,
                                    'totAuthVehicles' => 902,
                                    'totAuthTrailers' => 802,
                                    'OcAddress' => [
                                        'addressLine1' => '2_a1',
                                        'addressLine2' => '2_a2',
                                        'addressLine3' => '2_a3',
                                        'addressLine4' => null,
                                        'town' => '2t',
                                        'postcode' => 'Z_last',
                                        'countryCode' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'empty ocs' => [
                $case4,
                [
                    'data' => [
                        'tables' => [
                            'operating-centres' => []
                        ],
                    ],
                ],
            ],
        ];
    }
}
