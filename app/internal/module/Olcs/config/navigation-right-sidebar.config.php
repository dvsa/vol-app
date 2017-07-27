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
                            'id' => 'licence-quick-actions-create-variation',
                            'label' => 'Create variation',
                            'route' => 'lva-licence/variation',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-quick-actions-print-licence',
                            'label' => 'Print licence',
                            'route' => 'print_licence',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax',
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
                            'id' => 'licence-decisions-curtail',
                            'label' => 'Curtail',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'curtail',
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-revoke',
                            'label' => 'Revoke',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'revoke',
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-suspend',
                            'label' => 'Suspend',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'suspend',
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-surrender',
                            'label' => 'Surrender',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'surrender',
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-terminate',
                            'label' => 'Terminate',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'terminate',
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-reset-to-valid',
                            'label' => 'Reset to valid',
                            'route' => 'licence/reset-to-valid',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-undo-surrender',
                            'label' => 'Undo surrender',
                            'route' => 'licence/undo-surrender',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'licence-decisions-undo-terminate',
                            'label' => 'Undo termination',
                            'route' => 'licence/undo-terminate',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
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
            'pages' => array(),
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
                            'route' => 'licence/bus/create_variation',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-create-cancellation',
                            'label' => 'Create cancellation',
                            'route' => 'licence/bus/create_cancellation',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-print-reg-letter',
                            'label' => 'Print letter',
                            'route' => 'licence/bus/print/reg-letter',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax',
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-request-new-route-map',
                            'label' => 'Request new route map',
                            'route' => 'licence/bus/request_map',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-republish',
                            'label' => 'Republish',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'republish'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-quick-actions-request-withdrawn',
                            'label' => 'Withdraw',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'withdraw',
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
                                'action' => 'refuse-by-short-notice'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-decisions-refuse',
                            'label' => 'Refuse',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'refuse'
                            ],
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'bus-registration-decisions-admin-cancel',
                            'label' => 'Admin cancel',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'cancel'
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
        array(
            'id' => 'application',
            'label' => 'Application',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'application-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'application-quick-actions-view-full-application',
                            'label' => 'View full application',
                            'route' => 'lva-application/review',
                            'use_route_match' => true,
                            'class' => 'action--secondary',
                            'target' => '_blank',
                        ),
                        array(
                            'id' => 'application-quick-actions-publish-application',
                            'label' => 'Publish application',
                            'route' => 'lva-application/publish',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        )
                    ),
                ),
                array(
                    'id' => 'application-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'application-decisions-approve-schedule41',
                            'label' => 'Approve schedule 4/1',
                            'route' => 'lva-application/approve-schedule-41',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-refuse-schedule41',
                            'label' => 'Refuse schedule 4/1',
                            'route' => 'lva-application/refuse-schedule-41',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-reset-schedule41',
                            'label' => 'Reset schedule 4/1',
                            'route' => 'lva-application/reset-schedule-41',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-grant',
                            'label' => 'Grant application',
                            'route' => 'lva-application/grant',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-undo-grant',
                            'label' => 'Undo grant application',
                            'route' => 'lva-application/undo-grant',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-not-taken-up',
                            'label' => 'Not taken up',
                            'route' => 'lva-application/not-taken-up',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-revive-application',
                            'label' => 'Revive Application',
                            'route' => 'lva-application/revive-application',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-withdraw',
                            'label' => 'Withdraw application',
                            'route' => 'lva-application/withdraw',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-refuse',
                            'label' => 'Refuse application',
                            'route' => 'lva-application/refuse',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'application-decisions-submit',
                            'label' => 'Submit application for the operator',
                            'route' => 'lva-application/submit',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id' => 'transport-manager',
            'label' => 'Transport manager',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'transport-manager-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'transport_manager_details_review',
                            'label' => 'tm-quick-action-print-form',
                            'route' => 'transport_manager_review',
                            'use_route_match' => true,
                            'target' => '_blank',
                            'visible' => false
                        ),
                        array(
                            'id' => 'transport-manager-quick-actions-check-repute',
                            'label' => 'Check repute',
                            'uri' => '/', //set by the listener on page load
                            'target' => '_blank',
                            'visible' => false
                        ),
                        array(
                            'id' => 'transport-manager-quick-actions-remove',
                            'label' => 'tm-quick-action-remove',
                            'route' => 'transport-manager/can-remove',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'transport-manager-quick-actions-merge',
                            'label' => 'tm-quick-action-merge',
                            'route' => 'transport-manager/merge',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'transport-manager-quick-actions-unmerge',
                            'label' => 'tm-quick-action-unmerge',
                            'route' => 'transport-manager/unmerge',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'transport-manager-quick-actions-undo-disqualification',
                            'label' => 'tm-quick-action-undo-disqualification',
                            'route' => 'transport-manager/undo-disqualification',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        )
                    ),
                ),
            ),
        ),
        array(
            'id' => 'operator',
            'label' => 'Operator',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'operator-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'operator-decisions-disqualify',
                            'label' => 'Disqualify operator',
                            'route' => 'operator/disqualify',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                        array(
                            'id' => 'operator-decisions-merge',
                            'label' => 'Merge operator',
                            'route' => 'operator/merge',
                            'use_route_match' => true,
                            'class' => 'action--secondary js-modal-ajax'
                        ),
                    ),
                ),
            ),
        ),
    ),
);
