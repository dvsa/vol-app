<?php

$sectionConfig = new \Common\Service\Data\SectionConfig();
$sections = $sectionConfig->getAllReferences();
$applicationDetailsPages = array();
$licenceDetailsPages = array();
$variationDetailsPages = array();

foreach ($sections as $section) {
    $applicationDetailsPages[] = array(
        'id' => 'application_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-application/' . $section,
        'use_route_match' => true
    );

    $licenceDetailsPages[] = array(
        'id' => 'licence_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-licence/' . $section,
        'use_route_match' => true
    );

    $variationDetailsPages[] = array(
        'id' => 'variation_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-variation/' . $section,
        'use_route_match' => true
    );
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
                            'action' => 'details',
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
                            'id' => 'case_details_legacy_offence',
                            'label' => 'Legacy Offences',
                            'route' => 'offence',
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
                            'label' => 'Statements',
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
                            'id' => 'case_details_conditions_undertakings',
                            'label' => 'Conditions & Undertakings',
                            'route' => 'case_conditions_undertakings',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_details_impounding',
                            'label' => 'Impoundings',
                            'route' => 'case_details_impounding',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_opposition',
                    'label' => 'Opposition',
                    'route' => 'case_opposition',
                    'action' => 'index',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_opposition_add',
                            'label' => 'Add Opposition',
                            'route' => 'case_opposition',
                            'action' => 'add',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_opposition_edit',
                            'label' => 'Edit Opposition',
                            'route' => 'case_opposition',
                            'action' => 'edit',
                            'use_route_match' => true,
                        ),
                    )
                ),
                array(
                    'id' => 'case_submissions',
                    'label' => 'Submissions',
                    'route' => 'submission',
                    'action' => 'index',
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
                            'label' => 'Appeal and stays',
                            'route' => 'case_hearing_appeal',
                            'action' => 'details',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_hearings_appeals_public_inquiry',
                            'label' => 'Public Inquiry',
                            'route' => 'case_pi',
                            'action' => 'index',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'case_hearings_appeals_public_inquiry_add',
                                    'label' => 'internal-pi-hearing-add',
                                    'route' => 'case_pi_hearing',
                                    'action' => 'add',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'case_hearings_appeals_public_inquiry_edit',
                                    'label' => 'internal-pi-hearing-edit',
                                    'route' => 'case_pi_hearing',
                                    'action' => 'edit',
                                    'use_route_match' => true
                                ),
                            ),
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
                    'route' => 'processing',
                    'action' => 'overview',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case_processing_decisions',
                            'label' => 'Decisions',
                            'route' => 'processing_decisions',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_in_office_revocation',
                            'label' => 'In office revocation',
                            'route' => 'processing_in_office_revocation',
                            'action' => 'index',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_history',
                            'label' => 'History',
                            'route' => 'processing_history',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_tasks',
                            'label' => 'Tasks',
                            'route' => 'processing_tasks',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ),
                        array(
                            'id' => 'case_processing_notes',
                            'label' => 'Notes',
                            'route' => 'case_processing_notes',
                            'action' => 'index',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'case_processing_notes_add',
                                    'label' => 'case-note-add-label',
                                    'route' => 'case_processing_notes/add-note',
                                    'action' => 'add',

                                ),
                                array(
                                    'id' => 'case_processing_notes_edit',
                                    'label' => 'case-note-edit-label',
                                    'route' => 'case_processing_notes/modify-note',
                                    'action' => 'edit',

                                )
                            )
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
                    'route' => 'lva-licence',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'licence_details_overview',
                            'label' => 'internal-licence-details-breadcrumb',
                            'route' => 'lva-licence',
                            'use_route_match' => true,
                            'pages' => $licenceDetailsPages
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
                                    'use_route_match' => true
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
                        ),
                        array(
                            'id' => 'licence_fees',
                            'label' => 'Fees',
                            'route' => 'licence/fees',
                            'use_route_match' => true
                        ),
                    )
                )
            )
        ),
        array(
            'id' => 'create_application',
            'label' => 'Create application',
            'route' => 'create_application',
            'use_route_match' => true
        ),
        array(
            'id' => 'application',
            'label' => 'Application',
            'route' => 'lva-application',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'application_details',
                    'label' => 'Application details',
                    'route' => 'lva-application',
                    'use_route_match' => true,
                    'pages' => $applicationDetailsPages
                ),
                array(
                    'id' => 'application_case',
                    'label' => 'Cases',
                    'route' => 'lva-application/case',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_environmental',
                    'label' => 'Environmental',
                    'route' => 'lva-application/environmental',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_document',
                    'label' => 'Docs & attachments',
                    'route' => 'lva-application/document',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_processing',
                    'label' => 'Processing',
                    'route' => 'lva-application/processing',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_fee',
                    'label' => 'Fees',
                    'route' => 'lva-application/fees',
                    'use_route_match' => true
                )
            )
        ),
        array(
            'id' => 'variation',
            'label' => 'Variation application',
            'route' => 'lva-variation',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'variation_details',
                    'label' => 'Variation details',
                    'route' => 'lva-variation',
                    'use_route_match' => true,
                    'pages' => $variationDetailsPages
                )
            )
        )
    )
);
