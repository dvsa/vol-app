<?php

namespace Olcs\Controller\IrhpPermits;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * IRHP Application Docs Controller
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpApplicationDocsController extends AbstractIrhpPermitController implements IrhpApplicationControllerInterface
{
    use ControllerTraits\DocumentActionTrait,
        ControllerTraits\DocumentSearchTrait,
        ShowIrhpApplicationNavigationTrait;

    /**
     * Get configured document form
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\Form\FormInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->getDocumentFilters();
        $form = $this->getDocumentForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showDocs'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'This application only',
            ]
        );

        return $form;
    }

    /**
     * Table to use
     *
     * @see \Olcs\Controller\Traits\DocumentSearchTrait
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
        return 'licence/irhp-application-docs';
    }

    /**
     * Route params for document action redirects
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return [
            'irhpAppId' => $this->getFromRoute('irhpAppId'),
            'licence' => $this->getFromRoute('licence')
        ];
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
                'irhpApplication' => $this->getFromRoute('irhpAppId'),
            ]
        );
    }

    /**
     * Get view model for document action
     *
     * @see \Olcs\Controller\Traits\DocumentActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        return $this->getView(
            [
                'table' => $this->getDocumentsTable($filters)
            ]
        );
    }
}
