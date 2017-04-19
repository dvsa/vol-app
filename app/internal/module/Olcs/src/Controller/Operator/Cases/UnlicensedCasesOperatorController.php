<?php

/**
 * Unlicensed Cases Operator Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Operator\Cases;

use Dvsa\Olcs\Transfer\Query\Organisation\UnlicensedCases as OrganisationWithCases;
use Olcs\Controller\Operator\OperatorController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits\CheckForCrudAction;

/**
 * Unlicensed Cases Operator Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedCasesOperatorController extends OperatorController
{
    use CheckForCrudAction;

    /**
     * getLeftView
     *
     * @return null
     */
    public function getLeftView()
    {
        return null;
    }

    /**
     * casesAction
     *
     * @return ViewModel
     */
    public function casesAction()
    {
        $params = [
            'id'    => $this->getQueryOrRouteParam('organisation'),
            'page'  => $this->getQueryOrRouteParam('page', 1),
            'sort'  => $this->getQueryOrRouteParam('sort', 'createdOn'),
            'order' => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit' => $this->getQueryOrRouteParam('limit', 10),
            'query' => $this->getRequest()->getQuery()->toArray(),
        ];

        $response = $this->handleQuery(OrganisationWithCases::create($params));
        $result = $response->getResult();

        $licenceId = $result['licenceId'];

        $this->checkForCrudAction('case', ['licence' => $licenceId], 'case');
        $view = $this->getViewWithOrganisation();

        $cases = $result['cases']['result'];
        $view->{'table'} = $this->getTable('cases', $cases, $params);

        $view->setTemplate('pages/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }
}
