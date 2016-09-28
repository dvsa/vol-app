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
        'ZfcRbac\Guard\RoutePermissionsGuard' =>[
            // OLCS Module Routes
            '*processing/notes*' => [RefData::PERMISSION_INTERNAL_NOTES],
            '*case*' => [RefData::PERMISSION_INTERNAL_CASE],
            '*documents*' => [RefData::PERMISSION_INTERNAL_DOCUMENTS],
            '*docs*' => [RefData::PERMISSION_INTERNAL_DOCUMENTS],
            'fetch_tmp_document' => [RefData::PERMISSION_INTERNAL_DOCUMENTS],
            'note' => [RefData::PERMISSION_INTERNAL_NOTES],

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
            'admin-dashboard/admin-financial-standing*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-public-holiday*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-team-management*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-partner-management*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-printer-management*' => [ RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-user-management*' => [RefData::PERMISSION_CAN_MANAGE_USER_INTERNAL],
            'admin-dashboard/admin-system-parameters*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/admin-system-info-message*' => [RefData::PERMISSION_INTERNAL_ADMIN],
            'admin-dashboard/task-allocation-rules*' => [RefData::PERMISSION_INTERNAL_ADMIN],

            // All Internal users can see your account, to change their details, password etc
            'admin-dashboard/admin-your-account*' => [
                RefData::PERMISSION_INTERNAL_VIEW,
            ],

            // Other admin pages require mininmum internal-edit permission
            'admin-dashboard*' => [
                RefData::PERMISSION_INTERNAL_EDIT,
            ],

            // Global route rule needs to be last
            '*' => [
                RefData::PERMISSION_INTERNAL_VIEW,
            ],
        ]
    ]
];
