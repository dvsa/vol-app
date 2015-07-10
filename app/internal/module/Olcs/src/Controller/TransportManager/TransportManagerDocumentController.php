<?php

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\Traits;

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerDocumentController extends TransportManagerController
{
    use Traits\DocumentActionTrait;
    use Traits\DocumentSearchTrait;
    use Traits\ListDataTrait;

    /**
     * @var string
     */
    protected $section = 'documents';

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'transport-manager/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['transportManager' => $this->getFromRoute('transportManager')];
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $transportManager = $this->getFromRoute('transportManager');

        $filters = $this->mapDocumentFilters(['transportManager' => $transportManager]);

        $table = $this->getDocumentsTable($filters);
        $form  = $this->getDocumentForm($filters);

        return $this->getViewWithTm(['table' => $table, 'form'  => $form]);
    }
}
