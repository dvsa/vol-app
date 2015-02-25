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
                            'route' => 'lva-licence/variation',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
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
                            'route' => 'processing_decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'add',
                                'decision' => 'tm_decision_rnl',
                            ],
                            'caseType' => 'tm',
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'case-decisions-transport-manager-declare-unfit',
                            'label' => 'Declare unfit',
                            'route' => 'processing_decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'add',
                                'decision' => 'tm_decision_rl',
                            ],
                            'caseType' => 'tm',
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'case-decisions-transport-manager-no-further-action',
                            'label' => 'No further action',
                            'route' => 'processing_decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'add',
                                'decision' => 'tm_decision_noa',
                            ],
                            'caseType' => 'tm',
                            'class' => 'action--secondary js-modal-ajax'
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
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-create-cancellation',
                            'label' => 'Create cancellation',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-request-new-route-map',
                            'label' => 'Request new route map',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-republish',
                            'label' => 'Re-publish',
                            'route' => 'dashboard',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-request-withdrawn',
                            'label' => 'Withdraw',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'status',
                                'status' => 'breg_s_withdrawn'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
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
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'grant'
                            ]
                        ),
                        array(
                            'id' => 'bus-registration-decisions-refuse-by-short-notice',
                            'label' => 'Refuse by short notice',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'status',
                                'status' => 'sn_refused'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-decisions-refuse',
                            'label' => 'Refuse',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'status',
                                'status' => 'breg_s_refused'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-decisions-admin-cancel',
                            'label' => 'Admin cancel',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'status',
                                'status' => 'breg_s_admin'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-decisions-reset-registration',
                            'label' => 'Reset',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'reset'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                    ),
                ),
            ),
        ),
    ),
);
