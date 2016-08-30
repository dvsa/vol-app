<?php

namespace Olcs\Controller\Bus\Docs;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusDocsController extends AbstractController implements BusRegControllerInterface, LeftViewProvider
{
    use ControllerTraits\DocumentActionTrait,
        ControllerTraits\DocumentSearchTrait;

    /**
     * Get configured document form
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->mapDocumentFilters(
            [
                'licence' => $this->getFromRoute('licence')
            ]
        );

        return $this->getDocumentForm($filters);
    }

    /**
     * Table to use
     *
     * @see Olcs\Controller\Traits\DocumentSearchTrait
     * @return string
     */
    protected function getDocumentTableName()
    {
        return 'documents';
    }

    /**
     * Route (prefix) for document action redirects
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'licence/bus-docs';
    }

    /**
     * Route params for document action redirects
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return [
            'busRegId' => $this->getFromRoute('busRegId'),
            'licence' => $this->getFromRoute('licence')
        ];
    }

    /**
     * Get view model for document action
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->mapDocumentFilters(
            [
                'licence' => $this->getFromRoute('licence')
            ]
        );

        return $this->getView(
            [
                'table' => $this->getDocumentsTable($filters)
            ]
        );
    }
}
