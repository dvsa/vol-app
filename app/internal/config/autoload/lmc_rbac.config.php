<?php

/*
 * RoutePermissionGuard - Configuration is '<route_id>' => '<permission_name>'. Order is important, once
 * a route is matched, all others are ignored.
 * @see https://github.com/ZF-Commons/zfc-rbac/blob/master/docs/README.md
 *
 * NOTE: Any zfc_rbac configuration within any Module will be merged with other modules, hence this
 * global file where the order can be controlled and easily debugged.
 *
 */
use Common\RefData;

return [
    'guards' => [
        \LmcRbacMvc\Guard\RoutePermissionsGuard::class => [
            // OLCS Module Routes
            '*processing/notes*' => [RefData::PERMISSION_INTERNAL_NOTES],
            '*case*' => [RefData::PERMISSION_INTERNAL_CASE],
            'conviction_ajax*' => [RefData::PERMISSION_INTERNAL_CASE],
            'conviction*' => [RefData::PERMISSION_INTERNAL_CASE],
            'offence*' => [RefData::PERMISSION_INTERNAL_CASE],
            'submission*' => [RefData::PERMISSION_INTERNAL_CASE],
            '*surrender*' => [RefData::PERMISSION_INTERNAL_EDIT],

            '*processing*' => [RefData::PERMISSION_INTERNAL_PROCESSING],

            'licence/opposition*' => [RefData::PERMISSION_INTERNAL_OPPOSITION],
            'lva-application/opposition*' => [RefData::PERMISSION_INTERNAL_OPPOSITION],

            '*documents*' => [RefData::PERMISSION_INTERNAL_DOCUMENTS],
            '*docs*' => [RefData::PERMISSION_INTERNAL_DOCUMENTS],
            '*fees*' => [RefData::PERMISSION_INTERNAL_FEES],
            'fetch_tmp_document' => [RefData::PERMISSION_INTERNAL_DOCUMENTS],
            'note' => [RefData::PERMISSION_INTERNAL_NOTES],

            '*conversation*' => [RefData::PERMISSION_CAN_LIST_CONVERSATIONS],

            // CLI module Routes
            'batch-licence-status' => ['*'],
            'batch-cns' => ['*'],
            'process-queue' => ['*'],
            'inspection-request-email' => ['*'],
            'process-inbox' => ['*'],
            'enqueue-ch-compare' => ['*'],
            'not-found' => ['*'],
            'server-error' => ['*'],
            'create-translation-csv' => ['*'],

            // Admin Module Routes
            'admin-dashboard/admin-report/upload*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-financial-standing*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-public-holiday*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-team-management*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-partner-management*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-printer-management*' => [ RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-user-management*' => [RefData::PERMISSION_CAN_MANAGE_USER_INTERNAL],
            'admin-dashboard/admin-system-parameters*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-data-retention*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-system-info-message*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/task-allocation-rules*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-feature-toggle*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-permits*' => [RefData::PERMISSION_INTERNAL_PERMITS],
            'admin-dashboard/admin-email-templates' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/content-management*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-publication*' => [RefData::PERMISSION_INTERNAL_PUBLICATIONS],
            'admin-dashboard/admin-fee-rates*' => [RefData::PERMISSION_SYSTEM_ADMIN],
            'admin-dashboard/admin-bus-registration*' => [RefData::PERMISSION_INTERNAL_EDIT],
            'admin-dashboard/admin-presiding-tcs*' => [RefData::PERMISSION_SYSTEM_ADMIN],

            // All Internal users can see your account, to change their details, password etc
            'admin-dashboard/admin-your-account*' => [RefData::PERMISSION_INTERNAL_VIEW],

            // Other admin pages require mininmum internal-edit permission
            'admin-dashboard*' => [RefData::PERMISSION_INTERNAL_EDIT],

            // Quick action routes
            'print_licence' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-licence/variation' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/active-licence-check' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/reset-to-valid' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/undo-surrender' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/undo-terminate' => [RefData::PERMISSION_INTERNAL_EDIT],

            //  Operator
            'operator/new-application' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/disqualify' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/merge' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/irfo/gv-permits/*' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/irfo/psv-authorisations/*' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/documents/upload' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/documents/delete' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/documents/relink' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/documents/finalise' => [RefData::PERMISSION_INTERNAL_EDIT],
            'operator/users' => [RefData::PERMISSION_INTERNAL_EDIT],

            // Application / Variation
            'lva-application/submit' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/submit' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/publish' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/publish' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/grant' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/grant' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/undo-grant' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/not-taken-up' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/review' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/review' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/withdraw' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/withdraw' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/refuse' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/refuse' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/revive-application' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/revive-application' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/approve-schedule-41' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/approve-schedule-41' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/reset-schedule-41' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/reset-schedule-41' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-application/refuse-schedule-41' => [RefData::PERMISSION_INTERNAL_EDIT],
            'lva-variation/refuse-schedule-41' => [RefData::PERMISSION_INTERNAL_EDIT],

            // Transport Manager
            'transport-manager/can-remove' => [RefData::PERMISSION_INTERNAL_EDIT],
            'transport-manager/merge' => [RefData::PERMISSION_INTERNAL_EDIT],
            'transport-manager/unmerge' => [RefData::PERMISSION_INTERNAL_EDIT],
            'transport-manager/undo-disqualification' => [RefData::PERMISSION_INTERNAL_EDIT],

            // Bus reg
            'licence/bus/create_variation' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/bus/create_cancellation' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/bus/print/reg-letter' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/bus/request_map' => [RefData::PERMISSION_INTERNAL_EDIT],
            'licence/bus-processing/decisions' => [RefData::PERMISSION_INTERNAL_EDIT],

            // Global route rule needs to be last
            '*' => [RefData::PERMISSION_INTERNAL_VIEW],
        ]
    ],
    'redirect_strategy' => [
        'redirect_when_connected'        => false,
        'redirect_to_route_disconnected' => 'auth/login/GET',
        'append_previous_uri'            => true,
        'previous_uri_query_key'         => 'goto'
    ],
];
