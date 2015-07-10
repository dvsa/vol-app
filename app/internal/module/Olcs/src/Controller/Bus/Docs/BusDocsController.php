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

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusDocsController extends BusController
{

    use Traits\DocumentActionTrait;
    use Traits\DocumentSearchTrait;
    use Traits\ListDataTrait;

    protected $layoutFile = 'layout/docs-attachments-list';
    protected $section = 'docs';
    protected $subNavRoute = 'licence_bus_docs';

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
        $form  = $this->getDocumentForm($filters);

        return $this->getView(['table' => $table, 'form'  => $form]);
    }
}
