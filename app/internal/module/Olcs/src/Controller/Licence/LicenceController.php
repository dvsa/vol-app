<?php

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Traits;
use Olcs\Controller\Lva;

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceController extends AbstractController implements LicenceControllerInterface
{
    use Lva\Traits\LicenceControllerTrait,
        Traits\TaskSearchTrait,
        Traits\DocumentSearchTrait,
        Traits\DocumentActionTrait,
        Traits\FeesActionTrait;

    protected $pageLayout = 'licence-section';

    /**
     * Shows fees table
     */
    public function feesAction()
    {
        $response = $this->checkActionRedirect('licence');
        if ($response) {
            return $response;
        }

        return $this->commonFeesAction($this->params()->fromRoute('licence'));
    }

    public function payFeesAction()
    {
        $this->pageLayout = null;
        return $this->commonPayFeesAction('licence', $this->params('licence'));
    }

    public function detailsAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function casesAction()
    {
        $this->checkForCrudAction('case', [], 'case');
        $view = $this->getViewWithLicence();

        $params = [
            'licence' => $this->params()->fromRoute('licence'),
            'page'    => $this->params()->fromRoute('page', 1),
            'sort'    => $this->params()->fromRoute('sort', 'id'),
            'order'   => $this->params()->fromRoute('order', 'desc'),
            'limit'   => $this->params()->fromRoute('limit', 10),
        ];

        $bundle = array(
            'children' => array(
                'caseType' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $results = $this->makeRestCall('Cases', 'GET', $params, $bundle);

        $view->{'table'} = $this->getTable('case', $results, $params);

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }

    public function oppositionAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('pages/placeholder');

        return $this->renderView($view);
    }


    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'licence/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return array(
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
        $filters = $this->mapDocumentFilters(
            array('licenceId' => $this->getFromRoute('licence'))
        );

        return $this->getViewWithLicence(
            array(
                'table' => $this->getDocumentsTable($filters),
                'form'  => $this->getDocumentForm($filters)
            )
        );
    }

    public function busAction()
    {
        $searchData = array(
            'licence' => $this->getFromRoute('licence'),
            'page' => 1,
            'sort' => 'regNo',
            'order' => 'DESC',
            'limit' => 10
        );

        $filters = array_merge(
            $searchData,
            $this->getRequest()->getQuery()->toArray()
        );

        // if status is set to all
        if (isset($filters['status']) && !$filters['status']) {
            unset($filters['status']);
        }

        $resultData = $this->makeRestCall('BusRegSearchView', 'GET', $filters, []);

        $table = $this->getTable(
            'busreg',
            $resultData,
            array_merge(
                $filters,
                array('query' => $this->getRequest()->getQuery())
            ),
            true
        );

        $form = $this->getForm('bus-reg-list');
        $form->remove('csrf'); //we never post
        $form->setData($filters);

        $this->setTableFilters($form);

        $this->loadScripts(['forms/filter']);

        $view = $this->getViewWithLicence(
            array(
                'table' => $table
            )
        );

        $view->setTemplate('layout/bus-registrations-list');

        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

        return $this->renderView($view);
    }

    /**
     * This method is to assist the heirachical nature of zend
     * navigation when parent pages need to also be siblings
     * from a breadcrumb and navigation point of view.
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('licence/details/overview', [], [], true);
    }

    protected function renderLayout($view)
    {
        $tmp = $this->getViewWithLicence($view->getVariables());
        $view->setVariables($tmp->getVariables());

        return $this->renderView($view);
    }
}
