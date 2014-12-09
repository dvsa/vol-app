<?php

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Docs;

use Olcs\Controller\Bus\BusController;
use Olcs\Controller\Traits;

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDocsController extends BusController
{

    use Traits\DocumentActionTrait;
    use Traits\DocumentSearchTrait;
    use Traits\ListDataTrait;

    protected $layoutFile = 'licence/bus/layout-wide';
    protected $section = 'docs';
    protected $subNavRoute = 'licence_bus_docs';

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'licence_bus_docs';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return array(
            'busRegId' => $this->getFromRoute('busRegId'),
            'licence' => $this->getFromRoute('licence')
        );
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $licenceId = $this->getFromRoute('licence');

        $filters = $this->mapDocumentFilters(
            array('licenceId' => $licenceId)
        );

        $table = $this->getDocumentsTable($filters);
        $form  = $this->getDocumentForm($filters);

        $view = $this->getViewWithBusReg(
            array(
                'table' => $table,
                'form'  => $form
            )
        );

        $view->setTemplate('licence/bus/index');
        return $this->render($view);
    }
}
