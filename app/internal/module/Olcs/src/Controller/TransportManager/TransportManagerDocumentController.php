<?php

namespace Olcs\Controller\TransportManager;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerDocumentController extends TransportManagerController implements LeftViewProvider
{
    use Traits\DocumentActionTrait,
        Traits\DocumentSearchTrait,
        Traits\ListDataTrait;

    /**
     * Table to use
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentTableName()
    {
        return 'documents';
    }

    /**
     * @var string
     */
    protected $section = 'documents';

    /**
     * Process action - Index
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        // the action needs to be index. Otherwise the action name will get appended to urls in the TM menu
        return $this->documentsAction();
    }

    /**
     * Route (prefix) for document action redirects
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'transport-manager/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['transportManager' => $this->getFromRoute('transportManager')];
    }

    /**
     * Get view model for document action
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $transportManager = $this->getFromRoute('transportManager');

        $filters = $this->mapDocumentFilters(['transportManager' => $transportManager]);

        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithTm(['table' => $table]);
    }

    /**
     * Get Form
     *
     * @return \Zend\Form\FieldsetInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $transportManager = $this->getFromRoute('transportManager');

        $filters = $this->mapDocumentFilters(['transportManager' => $transportManager]);

        return $this->getDocumentForm($filters)
            ->remove('showDocs');
    }
}
