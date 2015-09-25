<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'lva-application/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['application' => $this->getFromRoute('application')];
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $application = $this->getFromRoute('application');
        $licence = $this->getLicenceIdForApplication($application);

        $filters = $this->mapDocumentFilters(['licence' => $licence]);

        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithApplication(['table' => $table]);
    }

    protected function getConfiguredDocumentForm()
    {
        $application = $this->getFromRoute('application');
        $licence = $this->getLicenceIdForApplication($application);

        $filters = $this->mapDocumentFilters(['licence' => $licence]);

        return $this->getDocumentForm($filters);
    }
}
