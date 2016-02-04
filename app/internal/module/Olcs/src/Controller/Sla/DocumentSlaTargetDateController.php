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
 * Abstract SLA Date Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DocumentSlaTargetDateController extends AbstractSlaTargetDateController
{
    protected $entityType = 'document';

    protected $redirectConfig = [
        'addsla' => [
            'route' => 'case_licence_docs_attachments',
            'action' => 'documents'
        ],
        'editsla' => [
            'route' => 'case_licence_docs_attachments',
            'action' => 'documents'
        ]
    ];
}
