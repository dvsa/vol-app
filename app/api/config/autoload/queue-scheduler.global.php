<?php

return [
    'queue_scheduler' => [
        'schedules' => [
            'process_queue_general' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => [
                    '--exclude', 'que_typ_ch_compare,que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing,que_typ_print,que_typ_disc_printing_print,que_typ_create_com_lic,que_typ_remove_deleted_docs,que_typ_permit_generate,que_typ_permit_print,que_typ_run_ecmt_scoring,que_typ_accept_ecmt_scoring,que_typ_irhp_permits_allocate'
                ],
                'local_enabled' => true,
            ],
            'process_queue_community_licences' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => ['--type', 'que_typ_create_com_lic'],
                'local_enabled' => true,
            ],
            'process_queue_disc_generation' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => ['--type', 'que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing'],
                'local_enabled' => true,
            ],
            'process_queue_print' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => ['--type', 'que_typ_print'],
                'local_enabled' => true,
            ],
            'process_queue_permit_generation' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => ['--type', 'que_typ_permit_generate'],
                'local_enabled' => true,
            ],
            'process_queue_ecmt_accept' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => ['--type', 'que_typ_accept_ecmt_scoring'],
                'local_enabled' => true,
            ],
            'process_queue_irhp_allocate' => [
                'interval' => 90,
                'command' => 'queue:process-queue',
                'args' => ['--type', 'que_typ_run_ecmt_scoring'],
                'local_enabled' => true,
            ],
            'transxchange_consumer' => [
                'interval' => 90,
                'command' => 'queue:transxchange-consumer',
                'args' => [],
                'local_enabled' => false,
            ],
            'process_company_profile' => [
                'interval' => 300,
                'command' => 'queue:process-company-profile',
                'args' => [],
                'local_enabled' => false,
            ],
        ],
    ],
];