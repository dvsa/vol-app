<?php

/**
 * Document SLA Date Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Sla;

use Olcs\Controller\Sla\AbstractSlaTargetDateController;
use Zend\View\Model\ViewModel;

/**
 * Abstract SLA Date Controller - Licence documents
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
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
