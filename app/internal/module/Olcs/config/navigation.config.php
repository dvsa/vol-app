<?php

// @todo Find a nicer way to re-use this config file
$applicationJourney = include(
    __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/config/journeys/application.journey.php'
);

$navItems = array();

$filter = new \Zend\Filter\Word\CamelCaseToDash();

foreach ($applicationJourney['Application']['sections'] as $sectionName => $section) {

    foreach ($section['subSections'] as $subSectionName => $subSection) {
        $label = strtolower('application.' . $filter->filter($sectionName) . '.' . $filter->filter($subSectionName));
        $navItems[] = array(
            'id' => 'application_details_' . $sectionName . '_' . $subSectionName,
            'label' => $label,
            'route' => 'Application/' . $sectionName . '/' . $subSectionName,
            'use_route_match' => true
        );
    }
}

return array(
    'label' => 'Home',
    'route' => 'dashboard',
    'use_route_match' => true,
    'pages' => array(
        array(
            'label' => 'Search',
            'route' => 'search',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'licence',
                    'label' => 'Licence',
                    'route' => 'licence',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'licence_details_overview',
                            'label' => 'internal-licence-details-breadcrumb',
                            'route' => 'licence/details/overview',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'licence_details_type_of_licence',
                                    'label' => 'internal-licence-details-type_of_licence',
                                    'route' => 'licence/details/type_of_licence',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_business_details',
                                    'label' => 'internal-licence-details-business_details',
                                    'route' => 'licence/details/business_details',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_address',
                                    'label' => 'internal-licence-details-address',
                                    'route' => 'licence/details/address',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_people',
                                    'label' => 'internal-licence-details-people',
                                    'route' => 'licence/details/people',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_operating_centre',
                                    'label' => 'internal-licence-details-operating_centre',
                                    'route' => 'licence/details/operating_centre',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_transport_manager',
                                    'label' => 'internal-licence-details-transport_manager',
                                    'route' => 'licence/details/transport_manager',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_vehicle',
                                    'label' => 'internal-licence-details-vehicle',
                                    'route' => 'licence/details/vehicle',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_safety',
                                    'label' => 'internal-licence-details-safety',
                                    'route' => 'licence/details/safety',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_condition_undertaking',
                                    'label' => 'internal-licence-details-condition_undertaking',
                                    'route' => 'licence/details/condition_undertaking',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_taxi_phv',
                                    'label' => 'internal-licence-details-taxi_phv',
                                    'route' => 'licence/details/taxi_phv',
                                    'use_route_match' => true
                                )
                            )
                        ),
                        array(
                            'id' => 'licence_bus',
                            'label' => 'Bus reg',
                            'route' => 'licence/bus',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence/cases',
                            'label' => 'Cases',
                            'route' => 'licence/cases',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'licence/cases/overview',
                                    'label' => 'Case Overview',
                                    'route' => 'case',
                                    'use_route_match' => true,
                                ),
                                array(
                                    'id' => 'licence/cases/add',
                                    'label' => 'Add Case',
                                    'route' => 'case',
                                    'action' => 'add',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence/cases/add',
                                    'label' => 'Edit Case',
                                    'route' => 'case',
                                    'action' => 'edit',
                                    'use_route_match' => true
                                )
                            )
                        ),
                        array(
                            'id' => 'licence_opposition',
                            'label' => 'Opposition',
                            'route' => 'licence/opposition',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_documents',
                            'label' => 'Docs & attachments',
                            'route' => 'licence/documents',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_processing',
                            'label' => 'Processing',
                            'route' => 'licence/processing',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'licence_processing_tasks',
                                    'label' => 'internal-licence-processing-tasks',
                                    'route' => 'licence/processing/tasks',
                                    'use_route_match' => true,
                                ),
                                array(
                                    'id' => 'licence_processing_notes',
                                    'label' => 'internal-licence-processing-notes',
                                    'route' => 'licence/processing/notes',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_processing_notes_add',
                                            'label' => 'internal-licence-processing-notes-add',
                                            'route' => 'licence/processing/add-note',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'id' => 'licence_processing_notes_modify',
                                            'label' => 'internal-licence-processing-notes-modify',
                                            'route' => 'licence/processing/modify-note',
                                            'use_route_match' => true
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        array(
            'id' => 'application',
            'label' => 'Application',
            'route' => 'Application',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'application_details',
                    'label' => 'Application details',
                    'route' => 'Application',
                    'use_route_match' => true,
                    'pages' => $navItems
                ),
                array(
                    'id' => 'application_case',
                    'label' => 'Cases',
                    'route' => 'Application/case',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_environmental',
                    'label' => 'Environmental',
                    'route' => 'Application/environmental',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_document',
                    'label' => 'Docs & attachments',
                    'route' => 'Application/document',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_processing',
                    'label' => 'Processing',
                    'route' => 'Application/processing',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_fee',
                    'label' => 'Fees',
                    'route' => 'Application/fee',
                    'use_route_match' => true
                ),
            )
        )
    )
);
