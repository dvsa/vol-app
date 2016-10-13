<?php

namespace Olcs\Controller\Cases\Docs;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Docs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseDocsController extends AbstractController implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\CaseControllerTrait,
        ControllerTraits\DocumentActionTrait,
        ControllerTraits\DocumentSearchTrait;

    /**
     * Table to use
     *
     * @see Olcs\Controller\Traits\DocumentSearchTrait
     * @return string
     */
    protected function getDocumentTableName()
    {
        return 'documents-with-sla';
    }

    /**
     * Route (prefix) for document action redirects
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'case_licence_docs_attachments';
    }

    /**
     * Route params for document action redirects
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['case' => $this->getFromRoute('case')];
    }

    /**
     * Get view model for document action
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        $table = $this->getDocumentsTable($filters);

        return $this->getView(['table' => $table]);
    }

    /**
     * Get document filters
     *
     * @return array
     */
    private function getDocumentFilters()
    {
        $case = $this->getCase();

        $filters = [
            'case' => $case['id'],
        ];

        switch ($case['caseType']['id']) {
            case 'case_t_tm':
                $filters['transportManager'] = $case['transportManager']['id'];
                break;
            default:
                $filters['licence'] = $case['licence']['id'];
                break;
        }

        return $this->mapDocumentFilters($filters);
    }

    /**
     * Get configured document form
     *
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->getDocumentFilters();

        $form = $this->getDocumentForm($filters);

        /** @var \Zend\Form\Element\Select $option */
        $option = $form->get('showDocs');
        $option->setValueOptions(
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-case-only',
            ]
        );

        return $form;
    }
}
