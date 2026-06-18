<?php

namespace Common\Service\Data;

use Common\RefData;
use Laminas\Filter\Word\UnderscoreToDash;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * Section Config
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SectionConfig
{
    /**
     * Holds the section config
     *
     * @var array
     */
    private $sections = [
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
                RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            ]
        ],
        'operating_centres' => [
            'restricted' => [
                RefData::LICENCE_TYPE_RESTRICTED,
                RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
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
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'transport_managers' => [
            'restricted' => [
                RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        ],
        'vehicles' => [
            'restricted' => [
                [
                    RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
                    ],
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    RefData::PSV_VEHICLE_SIZE_BOTH,
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    RefData::PSV_VEHICLE_SIZE_BOTH,
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
                    ],
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_SMALL,
                        RefData::PSV_VEHICLE_SIZE_BOTH,
                    ],
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_SMALL,
                        RefData::PSV_VEHICLE_SIZE_BOTH,
                    ],
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    RefData::PSV_VEHICLE_SIZE_BOTH,
                    'isOperatingSmallVehiclesSmallPart',
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    RefData::PSV_VEHICLE_SIZE_BOTH,
                    'isOperatingSmallVehiclesSmallPart',
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
                    ],
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_SMALL,
                        [
                            RefData::PSV_VEHICLE_SIZE_BOTH,
                            'isNotOperatingSmallVehiclesSmallPart',
                        ],
                    ],
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_SMALL,
                        [
                            RefData::PSV_VEHICLE_SIZE_BOTH,
                            'isNotOperatingSmallVehiclesSmallPart',
                        ],
                    ],
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        RefData::PSV_VEHICLE_SIZE_BOTH,
                    ],
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        RefData::PSV_VEHICLE_SIZE_BOTH,
                    ],
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
                    ],
                ],
            ],
        ],
        'psv_main_occupation_undertakings' => [
            'prerequisite' => [
                'vehicles_size',
            ],
            'restricted' => [
                [
                    'application',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        RefData::PSV_VEHICLE_SIZE_BOTH
                    ],
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                    ],
                    [
                        RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        RefData::PSV_VEHICLE_SIZE_BOTH
                    ],
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
                    ],
                ],
            ],
        ],
        'psv_operate_novelty' => [
            'prerequisite' => [
                'vehicles_size',
            ],
            'restricted' => [
                [
                    'application',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                ],
                [
                    'variation',
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    ],
                    [
                        'isLicenceUpgrade',
                        'vehicleAuthIncreased',
                    ],
                ],
            ],
        ],
        'trailers' => [
            'restricted' => [
                [
                    'external',
                    'licence',
                    RefData::LICENCE_CATEGORY_GOODS_VEHICLE
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
                    RefData::LICENCE_CATEGORY_PSV,
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
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
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                        // or
                        [
                            // PSV
                            RefData::LICENCE_CATEGORY_PSV,
                            // and restricted
                            RefData::LICENCE_TYPE_RESTRICTED
                        ]
                    ]
                ]
            ]
        ],
        'safety' => [
            'restricted' => [
                RefData::LICENCE_TYPE_RESTRICTED,
                RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        ],
        'conditions_undertakings' => [
            'restricted' => [
                [
                    // Must be one of these licence types
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
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
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'licence_history' => [
            'restricted' => [
                [
                    'application',
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ]
                ]
            ]
        ],
        'convictions_penalties' => [
            'restricted' => [
                [
                    'application',
                    [
                        RefData::LICENCE_TYPE_RESTRICTED,
                        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
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
        ],
    ];

    /**
     * Return all section references
     *
     * @return array
     */
    public function getAllReferences()
    {
        return array_keys($this->sections);
    }

    /**
     * Return route config for all sections
     *
     * @return array
     */
    public function getAllRoutes()
    {
        $sections = $this->getAllReferences();

        $dashFilter = new UnderscoreToDash();
        $camelFilter = new UnderscoreToCamelCase();

        $types = [
            'application' => [
                'identifier' => 'application'
            ],
            'licence' => [
                'identifier' => 'licence'
            ],
            'variation' => [
                'identifier' => 'application'
            ],
            'director_change' => [
                'identifier' => 'application'
            ],
        ];

        $routes = [];

        foreach ($types as $type => $options) {
            $typeController = 'Lva' . $camelFilter->filter($type);
            $baseRouteName = 'lva-' . $type;

            $routes[$baseRouteName] = [
                'type' => 'segment',
                'options' => [
                    'route' => sprintf('/%s/:%s[/]', $dashFilter->filter($type), $options['identifier']),
                    'constraints' => [
                        $options['identifier'] => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => $typeController,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ];

            $childRoutes = [];
            foreach ($sections as $section) {
                $routeKey = $dashFilter->filter($section);
                $sectionController = $camelFilter($section);

                $childRoutes[$section] = [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => $routeKey . '[/]',
                        'defaults' => [
                            'controller' => $typeController . '/' . $sectionController,
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ];
            }

            $routes[$baseRouteName]['child_routes'] = $childRoutes;
        }

        return $routes;
    }
}
