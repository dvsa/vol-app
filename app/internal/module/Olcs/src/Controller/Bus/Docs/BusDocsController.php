<?php

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Bus\Docs;

use Olcs\Controller\Bus\BusController;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusDocsController extends BusController
{
    use Traits\DocumentActionTrait,
        Traits\DocumentSearchTrait,
        Traits\ListDataTrait;

    protected $section = 'docs';
    protected $subNavRoute = 'licence_bus_docs';

    protected function getConfiguredDocumentForm()
    {
        $licence = $this->getFromRoute('licence');
        $filters = $this->mapDocumentFilters(['licence' => $licence]);
        return $this->getDocumentForm($filters);
    }

    /**
     * Table to use
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentTableName()
    {
        return 'documents';
    }

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'licence/bus-docs';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['busRegId' => $this->getFromRoute('busRegId'), 'licence' => $this->getFromRoute('licence')];
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $licence = $this->getFromRoute('licence');
        $filters = $this->mapDocumentFilters(['licence' => $licence]);

        $table = $this->getDocumentsTable($filters);

        return $this->getView(['table' => $table]);
    }
}
