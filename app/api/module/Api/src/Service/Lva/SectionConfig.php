<?php

namespace Dvsa\Olcs\Api\Service\Lva;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Laminas\Filter\Word\UnderscoreToCamelCase;

class SectionConfig
{
    private array $sections = [
        'type_of_licence' => [],
        'business_type' => [
            'prerequisite' => [
                'type_of_licence'
            ]
        ],
        'business_details' => [
            'prerequisite' => [
                [
                    'type_of_licence',
                    'business_type'
                ]
            ]
        ],
        'addresses' => [
            'prerequisite' => [
                'business_type'
            ]
        ],
        'people' => [
            'prerequisite' => [
                'business_type'
            ]
        ],
        'taxi_phv' => [
            'restricted' => [
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
            ]
        ],
        'operating_centres' => [
            'restricted' => [
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        ],
        'financial_evidence' => [
            'prerequisite' => [
                'operating_centres'
            ],
            'restricted' => [
                [
                    [
                        'application'
                    ],
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'transport_managers' => [
            'prerequisite' => [
                'operating_centres'
            ],
            'restricted' => [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        ],
        'vehicles' => [
            'prerequisite' => [
                'operating_centres'
            ],
            'restricted' => [
                [
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'vehicles_psv' => [
            'prerequisite' => [
                'operating_centres'
            ],
            'restricted' => [
                [
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'vehicles_size' => [
            'prerequisite' => [
                'operating_centres',
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                ],
            ],
        ],
        'psv_operate_large' => [
            'prerequisite' => [
                'vehicles_size',
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                ],
            ],
        ],
        'psv_operate_small' => [
            'prerequisite' => [
                'vehicles_size',
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    Application::PSV_VEHICLE_SIZE_BOTH,
                ],
            ],
        ],
        'psv_small_conditions' => [
            'prerequisite' => [
                'vehicles_size',
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        Application::PSV_VEHICLE_SIZE_SMALL,
                        Application::PSV_VEHICLE_SIZE_BOTH,
                    ],
                ],
            ],
        ],
        'psv_small_part_written' => [
            'prerequisite' => [
                'psv_operate_small',
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    Application::PSV_VEHICLE_SIZE_BOTH,
                    'isOperatingSmallVehiclesSmallPart',
                ],
            ],
        ],
        'psv_documentary_evidence_small' => [
            'prerequisite' => [
                'vehicles_size'
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        Application::PSV_VEHICLE_SIZE_SMALL,
                        [
                            Application::PSV_VEHICLE_SIZE_BOTH,
                            'isNotOperatingSmallVehiclesSmallPart',
                        ],
                    ],
                ],
            ],
        ],
        'psv_documentary_evidence_large' => [
            'prerequisite' => [
                'vehicles_size'
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                    ],
                    [
                        Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        Application::PSV_VEHICLE_SIZE_BOTH,
                    ],
                ],
            ],
        ],
        'psv_main_occupation_undertakings' => [
            'prerequisite' => [
                'vehicles_size'
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                    ],
                    [
                        Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        Application::PSV_VEHICLE_SIZE_BOTH
                    ],
                ],
            ]
        ],
        'psv_operate_novelty' => [
            'prerequisite' => [
                'vehicles_size',
            ],
            'restricted' => [
                [
                    'application',
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                ],
            ],
        ],
        'trailers' => [
            'restricted' => [
                [
                    'licence',
                    [
                        RefData::APP_VEHICLE_TYPE_HGV,
                        RefData::APP_VEHICLE_TYPE_MIXED,
                    ]
                ]
            ]
        ],
        'discs' => [
            'restricted' => [
                [
                    [
                        'licence',
                        'variation'
                    ],
                    Licence::LICENCE_CATEGORY_PSV,
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'community_licences' => [
            'restricted' => [
                [
                    // Only shown internally
                    [
                        'internal'
                    ],
                    // and must be either
                    [
                        // standard international
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                        // or
                        [
                            // PSV
                            Licence::LICENCE_CATEGORY_PSV,
                            // and restricted
                            Licence::LICENCE_TYPE_RESTRICTED
                        ]
                    ]
                ]
            ]
        ],
        'safety' => [
            'restricted' => [
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        ],
        'users' => [
            'restricted' => [
                [
                    'external',
                    'continuation'
                ]
            ]
        ],
        'conditions_undertakings' => [
            'restricted' => [
                [
                    // Must be one of these licence types
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    // and...
                    [
                        // either internal
                        'internal',
                        // or...
                        [
                            // external
                            'external',
                            // with conditions to show
                            'hasConditions',
                            // for licences
                            'licence',
                        ]
                    ]
                ]
            ]
        ],
        'financial_history' => [
            'restricted' => [
                [
                    'application',
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'licence_history' => [
            'restricted' => [
                [
                    'application',
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'convictions_penalties' => [
            'restricted' => [
                [
                    'application',
                    [
                        Licence::LICENCE_TYPE_RESTRICTED,
                        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        // external decalrations
        'undertakings' => [
            'restricted' => [
                [
                    // Must be variation or application
                    [
                        'application',
                        'variation'
                    ],
                    [
                        'external'
                    ],
                ]
            ],
        ],
        'declarations_internal' => [
            'restricted' => [
                [
                    // Must be variation or application
                    [
                        'application',
                        'variation'
                    ],
                    [
                        'internal'
                    ],
                ]
            ],
        ]
    ];

    protected bool $init = false;

    /**
     * @var ApplicationCompletion
     */
    protected $completion;

    protected function initSections()
    {
        if ($this->init === false) {
            $this->sections['financial_history']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['licence_history']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['convictions_penalties']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['financial_evidence']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['vehicles_size']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_operate_large']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_operate_small']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_small_part_written']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_small_conditions']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_documentary_evidence_small']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_documentary_evidence_large']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_operate_novelty']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            $this->sections['psv_main_occupation_undertakings']['restricted'][] = [
                'variation',
                $this->isNotUnchanged(...)
            ];

            // undertakings requires all sections (except itself)
            $undertakingsPreReqs = $this->getAllReferences();
            $key = array_search('undertakings', $undertakingsPreReqs);
            unset($undertakingsPreReqs[$key]);
            $this->sections['undertakings']['prerequisite'] = [$undertakingsPreReqs];
        }
    }

    public function isNotUnchanged($section)
    {
        $filter = new UnderscoreToCamelCase();

        $getter = 'get' . ucfirst((string) $filter->filter($section)) . 'Status';

        $status = $this->completion->$getter();

        return ($status != Application::VARIATION_STATUS_UNCHANGED);
    }

    public function setVariationCompletion(ApplicationCompletion $completion): void
    {
        $this->completion = $completion;
    }

    /**
     * Return all sections
     */
    public function getAll(): array
    {
        $this->initSections();

        return $this->sections;
    }

    /**
     * Return all section references
     */
    public function getAllReferences(): array
    {
        return array_keys($this->sections);
    }
}
