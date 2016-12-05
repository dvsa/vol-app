<?php

namespace Olcs\Controller\Licence\Docs;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Licence\LicenceController;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Licence Docs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceDocsController extends LicenceController implements LeftViewProvider
{
    use Traits\DocumentSearchTrait,
        Traits\DocumentActionTrait;

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
     * Route (prefix) for document action redirects
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'licence/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['licence' => $this->getFromRoute('licence')];
    }

    /**
     * Get Form
     *
     * @return \Zend\Form\FieldsetInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->mapDocumentFilters(['licence' => $this->getFromRoute('licence')]);

        return $this->getDocumentForm($filters)
            ->remove('showDocs');
    }

    /**
     * Get view model for document action
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->mapDocumentFilters(['licence' => $this->getFromRoute('licence')]);
        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithLicence(['table' => $table]);
    }
}
