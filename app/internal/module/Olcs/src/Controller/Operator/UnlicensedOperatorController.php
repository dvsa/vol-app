<?php

/**
 * Unlicensed Operator Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Query\Organisation\UnlicensedCases as OrganisationWithCases;
use Zend\View\Model\ViewModel;

/**
 * Unlicensed Operator Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedOperatorController extends OperatorController
{
    /**
     * @var string
     */
    protected $pageLayout = 'unlicensed-operator-section';

    /**
     * @var string
     */
    protected $layoutFile = 'layout/unlicensed-operator-subsection';

    /**
     * @var string
     */
    protected $subNavRoute;

    public function casesAction()
    {
        $this->subNavRoute = 'unlicensed_operator_cases';

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

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }

    public function vehiclesAction()
    {
        $this->subNavRoute = 'unlicensed_operator_profile';

        $view = new ViewModel();
        $view->setTemplate('pages/placeholder');

        return $this->renderView($view);
    }
}
