<?php

namespace Olcs\Controller\Application\Docs;

use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationDocsController extends ApplicationController implements LeftViewProvider
{
    use Traits\DocumentSearchTrait,
        Traits\DocumentActionTrait;

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
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'lva-application/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['application' => $this->getFromRoute('application')];
    }

    /**
     * Get document filters
     *
     * @return array
     */
    private function getDocumentFilters()
    {
        $appId = $this->getFromRoute('application');
        $licence = $this->getLicenceIdForApplication($appId);

        return $this->mapDocumentFilters(
            [
                'licence' => $licence,
                'application' => $this->getFromRoute('application'),
            ]
        );
    }

    /**
     * Get view model for document action
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithApplication(['table' => $table]);
    }

    /**
     * Get Customized Document Form
     *
     * @return \Zend\Form\FormInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->getDocumentFilters();

        return $this->getDocumentForm($filters);
    }
}
