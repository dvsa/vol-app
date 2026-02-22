<?php

namespace Olcs\Controller\Operator\Cases;

use Common\Controller\Traits\CheckForCrudAction;
use Dvsa\Olcs\Transfer\Query\Organisation\UnlicensedCases as OrganisationWithCases;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Operator\OperatorController;

class UnlicensedCasesOperatorController extends OperatorController
{
    use CheckForCrudAction;

    /**
     * getLeftView
     *
     * @return null
     */
    #[\Override]
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

        $httpResponse = $this->checkForCrudAction('case', ['licence' => $licenceId], 'case');
        if ($httpResponse instanceof Response) {
            return $httpResponse;
        }

        $view = $this->getViewWithOrganisation();

        $cases = $result['cases']['result'];
        $view->{'table'} = $this->getTable('cases', $cases, $params);

        $view->setTemplate('pages/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }
}
