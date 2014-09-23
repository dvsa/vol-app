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
    'use_route_match' => false,
    'pages' => array(
        array(
            'id' => 'case',
            'label' => 'Case',
            'route' => 'case',
            'action' => 'redirect',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'case_details',
                    'label' => 'Case details',
                    'route' => 'case',
                    'action' => 'redirect',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_details_overview',
                            'label' => 'Overview',
                            'route' => 'case',
                            'action' => 'overview',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_convictions',
                            'label' => 'Convictions',
                            'route' => 'conviction',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_annual_test_history',
                            'label' => 'Annual test history',
                            'route' => 'case_annual_test_history',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_prohibitions',
                            'label' => 'Prohibitions',
                            'route' => 'case_prohibition',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_penalties',
                            'label' => 'Penalties',
                            'route' => 'case_penalty',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_statements',
                            'label' => 'X Statements',
                            'route' => 'case_statement',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_complaints',
                            'label' => 'Complaints',
                            'route' => 'case_complaint',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_public_inquiry',
                            'label' => 'Public inquiry',
                            'route' => 'case_pi',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_conditions_undertakings',
                            'label' => 'Conditions & Undertakings',
                            'route' => 'case_conditions_undertakings',
                            'action' => 'conditions',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_impoundings',
                            'label' => 'X Impoundings',
                            'route' => 'case',
                            'action' => 'impoundings',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_oppositions',
                    'label' => 'Opposition',
                    'route' => 'case',
                    'action' => 'opositions',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_submission_list',
                            'label' => 'Submission List',
                            'route' => 'submission',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_submissions',
                    'label' => 'Submissions',
                    'route' => 'submission',
                    'action' => 'redirect',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_submission_list',
                            'label' => 'Submission List',
                            'route' => 'submission',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_submission_add',
                            'label' => 'Add Submission',
                            'route' => 'submission',
                            'action' => 'add',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_submission_edit',
                            'label' => 'Edit Submission',
                            'route' => 'submission',
                            'action' => 'edit',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_hearings_appeals',
                    'label' => 'Hearings & appeals',
                    'route' => 'case_hearing_appeal',
                    'action' => 'index',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_hearings_appeals_stays',
                            'label' => 'Appeals and stays',
                            'route' => 'case_hearing_appeal',
                            'action' => 'details',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_hearings_appeals_public_inquiry',
                            'label' => 'Public inquiry',
                            'route' => 'case_public_inquiry',
                            'action' => 'details',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_docs_attachments',
                    'label' => 'Docs & attachments',
                    'route' => 'case',
                    'action' => 'docs',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_docs_attachments_documents',
                            'label' => 'Docs',
                            'route' => 'case',
                            'action' => 'docs',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_processing',
                    'label' => 'Processing',
                    'route' => 'case',
                    'action' => 'index',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_processing_decisions',
                            'label' => 'Decisions',
                            'route' => 'case',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_revocation',
                            'label' => 'In office revokaction',
                            'route' => 'case',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_history',
                            'label' => 'History',
                            'route' => 'case',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_tasks',
                            'label' => 'Tasks',
                            'route' => 'case',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_notes',
                            'label' => 'Notes',
                            'route' => 'case',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                    )
                )
            ),
        ),
        array(
            'id' => 'case_add',
            'label' => 'Add Case',
            'route' => 'case',
            'action' => 'add',
            'use_route_match' => true
        ),
        array(
            'id' => 'case_edit',
            'label' => 'Edit Case',
            'route' => 'case',
            'action' => 'edit',
            'use_route_match' => true
        ),
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
                            'use_route_match' => true,
                            'pages' => array (
                                array(
                                    'id' => 'licence_bus_details',
                                    'label' => 'internal-licence-bus-details',
                                    'route' => 'licence/bus-details',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_details-service',
                                            'label' => 'internal-licence-bus-details-service',
                                            'route' => 'licence/bus-details/service',
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'id' => 'licence_bus_details-stop',
                                            'label' => 'internal-licence-bus-details-stop',
                                            'route' => 'licence/bus-details/stop',
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'id' => 'licence_bus_details-ta',
                                            'label' => 'internal-licence-bus-details-ta',
                                            'route' => 'licence/bus-details/ta',
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'id' => 'licence_bus_details-quality',
                                            'label' => 'internal-licence-bus-details-quality',
                                            'route' => 'licence/bus-details/quality',
                                            'use_route_match' => true,
                                        )
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_short',
                                    'label' => 'internal-licence-bus-short',
                                    'route' => 'licence/bus-short',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_short-placeholder',
                                            'label' => 'internal-licence-bus-short-placeholder',
                                            'route' => 'licence/bus-short/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_route',
                                    'label' => 'internal-licence-bus-route',
                                    'route' => 'licence/bus-route',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_route-placeholder',
                                            'label' => 'internal-licence-bus-route-placeholder',
                                            'route' => 'licence/bus-route/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_trc',
                                    'label' => 'internal-licence-bus-trc',
                                    'route' => 'licence/bus-trc',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_trc-placeholder',
                                            'label' => 'internal-licence-bus-trc-placeholder',
                                            'route' => 'licence/bus-trc/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_docs',
                                    'label' => 'internal-licence-bus-docs',
                                    'route' => 'licence/bus-docs',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_docs-placeholder',
                                            'label' => 'internal-licence-bus-docs-placeholder',
                                            'route' => 'licence/bus-docs/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_processing',
                                    'label' => 'internal-licence-bus-processing',
                                    'route' => 'licence/bus-processing',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_processing_notes',
                                            'label' => 'internal-licence-bus-processing-notes',
                                            'route' => 'licence/bus-processing/notes',
                                            'use_route_match' => true,
                                            'pages' => array(
                                                array(
                                                    'id' => 'licence_bus_processing_notes_add',
                                                    'label' => 'internal-licence-bus-processing-notes-add',
                                                    'route' => 'licence/bus-processing/add-note',
                                                    'use_route_match' => true
                                                ),
                                                array(
                                                    'id' => 'licence_bus_processing_notes_modify',
                                                    'label' => 'internal-licence-bus-processing-notes-modify',
                                                    'route' => 'licence/bus-processing/modify-note',
                                                    'use_route_match' => true
                                                )
                                            )
                                        )
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_fees',
                                    'label' => 'internal-licence-bus-fees',
                                    'route' => 'licence/bus-fees',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_fees-placeholder',
                                            'label' => 'internal-licence-bus-fees-placeholder',
                                            'route' => 'licence/bus-fees/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                            )
                        ),
                        array(
                            'id' => 'licence/cases',
                            'label' => 'Cases',
                            'route' => 'licence/cases',
                            'action' => 'cases',
                            'use_route_match' => true
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
