<?php

namespace Olcs\Controller\Licence\Docs;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
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
     * Get document filters
     *
     * @return array
     */
    private function getDocumentFilters()
    {
        return $this->mapDocumentFilters(
            [
                'licence' => $this->getFromRoute('licence'),
                'showDocs' => FilterOptions::EXCLUDE_IRHP,
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
        $filters = $this->getDocumentFilters();

        $form = $this->getDocumentForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showDocs'),
            [
                FilterOptions::EXCLUDE_IRHP => 'documents.filter.option.exclude-irhp',
            ]
        );

        return $form;
    }

    /**
     * Get view model for document action
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithLicence(['table' => $table]);
    }
}
