<?php

return [
    'id'    => 'root',
    'label' => 'Right sidebar',
    'route' => 'dashboard',
    'use_route_match' => false,
    'pages' => [
        [
            'id' => 'licence',
            'label' => 'Licence',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'licence-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'licence-quick-actions-create-variation',
                            'label' => 'Create variation',
                            'route' => 'lva-licence/variation',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-enable-file-uploads',
                            'label' => 'Enable file uploads',
                            'route' => 'licence/conversation/fileuploads/enable',
                            'visible' => false,
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-disable-file-uploads',
                            'label' => 'Disable file uploads',
                            'route' => 'licence/conversation/fileuploads/disable',
                            'visible' => false,
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-quick-actions-print-licence',
                            'label' => 'Print licence',
                            'route' => 'print_licence',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax',
                        ]
                    ],
                ],
                [
                    'id' => 'licence-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'licence-decisions-curtail',
                            'label' => 'Curtail',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'curtail',
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-revoke',
                            'label' => 'Revoke',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'revoke',
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-suspend',
                            'label' => 'Suspend',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'suspend',
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-surrender',
                            'label' => 'Surrender',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'surrender',
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-terminate',
                            'label' => 'Terminate',
                            'route' => 'licence/active-licence-check',
                            'use_route_match' => true,
                            'params' => [
                                'decision' => 'terminate',
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-reset-to-valid',
                            'label' => 'Reset to valid',
                            'route' => 'licence/reset-to-valid',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-undo-surrender',
                            'label' => 'Undo surrender',
                            'route' => 'licence/undo-surrender',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'licence-decisions-undo-terminate',
                            'label' => 'Undo termination',
                            'route' => 'licence/undo-terminate',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'case',
            'label' => 'Case',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [],
        ],
        [
            'id' => 'bus-registration',
            'label' => 'Bus Registration',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'bus-registration-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'bus-registration-quick-actions-create-variation',
                            'label' => 'Create variation',
                            'route' => 'licence/bus/create_variation',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-quick-actions-create-cancellation',
                            'label' => 'Create cancellation',
                            'route' => 'licence/bus/create_cancellation',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-quick-actions-print-reg-letter',
                            'label' => 'Print letter',
                            'route' => 'licence/bus/print/reg-letter',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax',
                        ],
                        [
                            'id' => 'bus-registration-quick-actions-request-new-route-map',
                            'label' => 'Request new route map',
                            'route' => 'licence/bus/request_map',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-quick-actions-republish',
                            'label' => 'Republish',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'republish'
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-quick-actions-request-withdrawn',
                            'label' => 'Withdraw',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'withdraw',
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ]
                    ],
                ],
                [
                    'id' => 'bus-registration-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'bus-registration-decisions-grant',
                            'label' => 'Grant',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'grant'
                            ]
                        ],
                        [
                            'id' => 'bus-registration-decisions-refuse-by-short-notice',
                            'label' => 'Refuse by short notice',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'refuse-by-short-notice'
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-decisions-refuse',
                            'label' => 'Refuse',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'refuse'
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-decisions-admin-cancel',
                            'label' => 'Admin cancel',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'cancel'
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'bus-registration-decisions-reset-registration',
                            'label' => 'Reset',
                            'route' => 'licence/bus-processing/decisions',
                            'use_route_match' => true,
                            'params' => [
                                'action' => 'reset'
                            ],
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'irhp-applications',
            'label' => 'IRHP Applications',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'irhp-application-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'irhp-application-quick-actions-cancel',
                            'label' => 'Cancel',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'cancel',
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'irhp-application-quick-actions-terminate',
                            'label' => 'Terminate',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'terminate',
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'irhp-application-quick-actions-reset-to-not-yet-submitted-from-cancelled',
                            'label' => 'Reset',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'resetToNotYetSubmittedFromCancelled',
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                    ],
                ],
                [
                    'id' => 'irhp-application-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'irhp-application-decisions-submit',
                            'label' => 'Submit',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'submit'
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'irhp-application-decisions-grant',
                            'label' => 'Grant application',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'grant'
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax',
                            'visible' => false
                        ],
                        [
                            'id' => 'irhp-application-decisions-withdraw',
                            'label' => 'Withdraw application',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'withdraw'
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'irhp-application-decisions-revive-from-withdrawn',
                            'label' => 'Revive application',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'reviveFromWithdrawn'
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'irhp-application-decisions-revive-from-unsuccessful',
                            'label' => 'Revive application',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'reviveFromUnsuccessful'
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'irhp-application-decisions-reset-to-not-yet-submitted-from-valid',
                            'label' => 'Reset to Not Yet Submitted',
                            'route' => 'licence/irhp-application/application',
                            'params' => [
                                'action' => 'resetToNotYetSubmittedFromValid'
                            ],
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'application',
            'label' => 'Application',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'application-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'application-quick-actions-view-full-application',
                            'label' => 'View full application',
                            'route' => 'lva-application/review',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary',
                            'target' => '_blank',
                        ],
                        [
                            'id' => 'application-enable-file-uploads',
                            'label' => 'Enable file uploads',
                            'route' => 'lva-application/conversation/fileuploads/enable',
                            'visible' => false,
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-disable-file-uploads',
                            'label' => 'Disable file uploads',
                            'route' => 'lva-application/conversation/fileuploads/disable',
                            'visible' => false,
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-quick-actions-publish-application',
                            'label' => 'Publish application',
                            'route' => 'lva-application/publish',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ]
                    ],
                ],
                [
                    'id' => 'application-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'application-decisions-approve-schedule41',
                            'label' => 'Approve schedule 4/1',
                            'route' => 'lva-application/approve-schedule-41',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-refuse-schedule41',
                            'label' => 'Refuse schedule 4/1',
                            'route' => 'lva-application/refuse-schedule-41',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-reset-schedule41',
                            'label' => 'Reset schedule 4/1',
                            'route' => 'lva-application/reset-schedule-41',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-grant',
                            'label' => 'Grant application',
                            'route' => 'lva-application/grant',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-undo-grant',
                            'label' => 'Undo grant application',
                            'route' => 'lva-application/undo-grant',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-not-taken-up',
                            'label' => 'Not taken up',
                            'route' => 'lva-application/not-taken-up',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-revive-application',
                            'label' => 'Revive Application',
                            'route' => 'lva-application/revive-application',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-withdraw',
                            'label' => 'Withdraw application',
                            'route' => 'lva-application/withdraw',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-refuse',
                            'label' => 'Refuse application',
                            'route' => 'lva-application/refuse',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'application-decisions-submit',
                            'label' => 'Submit application for the operator',
                            'route' => 'lva-application/submit',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'transport-manager',
            'label' => 'Transport manager',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'transport-manager-quick-actions',
                    'label' => 'Quick actions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'transport_manager_details_review',
                            'label' => 'tm-quick-action-print-form',
                            'route' => 'transport_manager_review',
                            'use_route_match' => true,
                            'target' => '_blank',
                            'visible' => false
                        ],
                        [
                            'id' => 'transport-manager-quick-actions-check-repute',
                            'label' => 'Check repute',
                            'uri' => '/', //set by the listener on page load
                            'target' => '_blank',
                            'visible' => false
                        ],
                        [
                            'id' => 'transport-manager-quick-actions-remove',
                            'label' => 'tm-quick-action-remove',
                            'route' => 'transport-manager/can-remove',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'transport-manager-quick-actions-merge',
                            'label' => 'tm-quick-action-merge',
                            'route' => 'transport-manager/merge',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'transport-manager-quick-actions-unmerge',
                            'label' => 'tm-quick-action-unmerge',
                            'route' => 'transport-manager/unmerge',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'transport-manager-quick-actions-undo-disqualification',
                            'label' => 'tm-quick-action-undo-disqualification',
                            'route' => 'transport-manager/undo-disqualification',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ]
                    ],
                ],
            ],
        ],
        [
            'id' => 'operator',
            'label' => 'Operator',
            'route' => 'dashboard',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'operator-decisions',
                    'label' => 'Decisions',
                    'route' => 'dashboard',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'operator-decisions-disqualify',
                            'label' => 'Disqualify operator',
                            'route' => 'operator/disqualify',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                        [
                            'id' => 'operator-decisions-merge',
                            'label' => 'Merge operator',
                            'route' => 'operator/merge',
                            'use_route_match' => true,
                            'class' => 'govuk-button govuk-button--secondary js-modal-ajax'
                        ],
                    ],
                ],
            ],
        ],
    ],
];
