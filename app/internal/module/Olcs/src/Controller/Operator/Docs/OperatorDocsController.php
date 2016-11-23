<?php

namespace Olcs\Controller\Operator\Docs;

use Olcs\Controller\Operator\OperatorController;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Operator Docs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatorDocsController extends OperatorController
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
        return 'operator/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['organisation' => $this->getFromRoute('organisation')];
    }

    /**
     * Get view model for document action
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->mapDocumentFilters(['irfoOrganisation' => $this->getFromRoute('organisation')]);

        return $this->getViewWithOrganisation(
            [
                'table' => $this->getDocumentsTable($filters),
                'documents' => true,
            ]
        );
    }

    /**
     * Get Form
     *
     * @return \Zend\Form\FieldsetInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->mapDocumentFilters(['irfoOrganisation' => $this->getFromRoute('organisation')]);

        return $this->getDocumentForm($filters)
            ->remove('showDocs');
    }
}
