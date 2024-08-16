<?php

namespace Olcs\Controller\Sla;

class LicenceDocumentSlaTargetDateController extends AbstractSlaTargetDateController
{
    protected $entityType = 'document';

    protected $redirectConfig = [
        'addsla' => [
            'route' => 'licence/documents',
            'action' => 'documents'
        ],
        'editsla' => [
            'route' => 'licence/documents',
            'action' => 'documents'
        ]
    ];
}
