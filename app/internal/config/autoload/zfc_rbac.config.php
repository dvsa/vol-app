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
return [
    'guards' => [
        'ZfcRbac\Guard\RoutePermissionsGuard' =>[

            // OLCS Module Routes
            '*processing/notes*' => ['internal-notes'],
            '*case*' => ['internal-case'],
            '*documents*' => ['internal-documents'],
            '*docs*' => ['internal-documents'],
            'fetch_tmp_document' => ['internal-documents'],
            'note' => ['internal-notes'],

            // CLI module Routes
            'batch-licence-status' => ['*'],
            'batch-cns' => ['*'],
            'process-queue' => ['*'],
            'inspection-request-email' => ['*'],
            'process-inbox' => ['*'],
            'enqueue-ch-compare' => ['*'],
            'not-found' => ['*'],
            'server-error' => ['*'],

            // Admin Module Routes
            'admin-dashboard/admin-financial-standing*' => ['internal-admin'],
            'admin-dashboard/admin-system-message*' => ['internal-admin'],
            'admin-dashboard/admin-public-holiday*' => ['internal-admin'],
            'admin-dashboard/admin-team-management*' => ['internal-admin'],
            'admin-dashboard/admin-partner-management*' => ['internal-admin'],
            'admin-dashboard/admin-printer-management*' => ['internal-admin'],
            'admin-dashboard/admin-user-management*' => ['can-manage-user-internal'],
            'admin-dashboard/admin-system-parameters*' => ['internal-admin'],

            // All Internal users can see my account, to change their details, password etc
            'admin-dashboard/admin-my-account*' => [
                'internal-view'
            ],

            // Other admin pages require mininmum internal-edit permission
            'admin-dashboard*' => [
                'internal-edit',
            ],

            // Global route rule needs to be last
            '*' => ['internal-view'],
        ]
    ]
];
