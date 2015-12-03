<?php

return [
    'guards' => [
        'ZfcRbac\Guard\RoutePermissionsGuard' =>[
            // OLCS Module pages
            'case_processing_notes' => ['internal-notes'],
            '*case*' => ['internal-case'],
            '*documents*' => ['internal-documents'],
            '*docs*' => ['internal-documents'],
            'fetch_tmp_document' => ['internal-documents'],
            'note' => ['internal-notes'],
            // cli module route
            'batch-licence-status' => ['*'],
            'batch-cns' => ['*'],
            'process-queue' => ['*'],
            'inspection-request-email' => ['*'],
            'process-inbox' => ['*'],
            'enqueue-ch-compare' => ['*'],
            'not-found' => ['*'],
            'server-error' => ['*'],

            // Admin Module Pages
            'admin-dashboard/admin-financial-standing*' => ['internal-admin'],
            'admin-dashboard/admin-payment-processing*' => ['internal-admin'],
            'admin-dashboard/admin-system-message' => ['internal-admin'],
            'admin-dashboard/admin-public-holiday' => ['internal-admin'],
            'admin-dashboard/admin-team-management' => ['internal-admin'],

            //'admin-dashboard/*' => ['internal-user'],

            // Global route rule needs to be last
            '*' => ['internal-view'],
        ]
    ]
];
