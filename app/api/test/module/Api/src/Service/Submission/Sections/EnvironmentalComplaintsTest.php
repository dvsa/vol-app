<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class EnvironmentalComplaintsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class EnvironmentalComplaintsTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\EnvironmentalComplaints::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = [
            'data' => [
                'tables' => [
                    'environmental-complaints' => [
                        [
                            'id' => 563,
                            'version' => 565,
                            'complainantForename' => 'fn22',
                            'complainantFamilyName' => 'sn22',
                            'description' => null,
                            'complaintDate' => '',
                            'ocAddress' => [
                                0 => [
                                    'address' => [
                                        'addressLine1' => '633_a1',
                                        'addressLine2' => '633_a2',
                                        'addressLine3' => '633_a3',
                                        'addressLine4' => null,
                                        'town' => '633t',
                                        'postcode' => 'pc6331PC',
                                        'countryCode' => null
                                    ]
                                ]
                            ],
                            'closeDate' => '',
                            'status' => 'ecst_open-desc',
                        ],
                        [
                            'id' => 543,
                            'version' => 545,
                            'complainantForename' => 'fn22',
                            'complainantFamilyName' => 'sn22',
                            'description' => null,
                            'complaintDate' => '03/05/2006',
                            'ocAddress' => [
                                0 => [
                                    'address' => [
                                        'addressLine1' => '633_a1',
                                        'addressLine2' => '633_a2',
                                        'addressLine3' => '633_a3',
                                        'addressLine4' => null,
                                        'town' => '633t',
                                        'postcode' => 'pc6331PC',
                                        'countryCode' => null
                                    ]
                                ]
                            ],
                            'closeDate' => '',
                            'status' => 'ecst_open-desc'
                        ],
                        [
                            'id' => 253,
                            'version' => 255,
                            'complainantForename' => 'fn22',
                            'complainantFamilyName' => 'sn22',
                            'description' => null,
                            'complaintDate' => '04/05/2006',
                            'ocAddress' => [
                                0 => [
                                    'address' => [
                                        'addressLine1' => '633_a1',
                                        'addressLine2' => '633_a2',
                                        'addressLine3' => '633_a3',
                                        'addressLine4' => null,
                                        'town' => '633t',
                                        'postcode' => 'pc6331PC',
                                        'countryCode' => null
                                    ]
                                ]
                            ],
                            'closeDate' => '',
                            'status' => 'ecst_open-desc'
                        ]
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
