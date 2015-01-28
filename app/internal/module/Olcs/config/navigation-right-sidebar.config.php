<?php
return array(
    'id'    => 'root',
    'label' => 'Right sidebar',
    'route' => 'dashboard',
    'use_route_match' => false,
    'pages' => array(
        array(
            'id' => 'licence',
            'label' => 'Licence',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'licence-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'licence-quick-actions-create-case',
                            'label' => 'Create case',
                            'route' => 'case',
                            'action' => 'add',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence-quick-actions-create-variation',
                            'label' => 'Create variation',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence-quick-actions-print-licence',
                            'label' => 'Print licence',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        )
                    ),
                ),
                array(
                    'id' => 'licence-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'licence-decisions-surrender',
                            'label' => 'Surrender',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence-decisions-curtail',
                            'label' => 'Curtail',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence-decisions-revoke',
                            'label' => 'Revoke',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id' => 'case',
            'label' => 'Case',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'case-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case-quick-actions-create-submission',
                            'label' => 'Create submission',
                            'route' => 'submission',
                            'action' => 'add',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'case-quick-actions-create-statement',
                            'label' => 'Create statement',
                            'route' => 'case_statement',
                            'action' => 'add',
                            'use_route_match' => true
                        )
                    ),
                ),
                array(
                    'id' => 'case-decisions-licence',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case-decisions-licence-surrender',
                            'label' => 'Surrender',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'caseType' => 'licence',
                        ),
                        array(
                            'id' => 'case-decisions-licence-curtail',
                            'label' => 'Curtail',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'caseType' => 'licence',
                        ),
                        array(
                            'id' => 'case-decisions-licence-revoke',
                            'label' => 'Revoke',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'caseType' => 'licence',
                        )
                    ),
                ),
                array(
                    'id' => 'case-decisions-transport-manager',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'case-decisions-transport-manager-repute-not-lost',
                            'label' => 'Repute not lost',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'caseType' => 'tm',
                        ),
                        array(
                            'id' => 'case-decisions-transport-manager-declare-unfit',
                            'label' => 'Declare unfit',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'caseType' => 'tm',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id' => 'bus-registration',
            'label' => 'Bus Registration',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'bus-registration-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'bus-registration-quick-actions-create-variation',
                            'label' => 'Create variation',
                            'route' => 'case',
                            'action' => 'add',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-create-cancellation',
                            'label' => 'Create cancellation',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-request-new-route-map',
                            'label' => 'Request new route map',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-republish',
                            'label' => 'Re-publish',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-request-withdrawn',
                            'label' => 'Withdrawn',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        )
                    ),
                ),
                array(
                    'id' => 'bus-registration-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'bus-registration-decisions-grant',
                            'label' => 'Grant',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-decisions-refuse-by-short-notice',
                            'label' => 'Refuse by short notice',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-decisions-refuse',
                            'label' => 'Refuse',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'bus-registration-decisions-admin-cancel',
                            'label' => 'Admin cancel',
                            'route' => 'dashboard',
                            'use_route_match' => true
                        ),
                    ),
                ),
            ),
        ),
    ),
);
