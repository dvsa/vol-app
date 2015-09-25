<?php

/**
 * Case Docs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Cases\Docs;

use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Cases\CaseController;


/**
 * Case Docs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseDocsController extends CaseController implements LeftViewProvider
{
    use ControllerTraits\DocumentActionTrait,
        ControllerTraits\DocumentSearchTrait;

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'case_licence_docs_attachments';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['case' => $this->getFromRoute('case')];
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        $table = $this->getDocumentsTable($filters);

        return $this->getView(['table' => $table]);
    }

    protected function getDocumentFilters()
    {
        $case = $this->getCase();

        $filters = ['case' => $case['id']];
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

    protected function getConfiguredDocumentForm()
    {
        $filters = $this->getDocumentFilters();

        return $this->getDocumentForm($filters);
    }
}
