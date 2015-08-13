<?php

/**
 * Unlicensed Operator Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Query\Organisation\UnlicensedCases as OrganisationWithCases;

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
    protected $subNavRoute = 'unlicensed_operator_cases';

    /**
     * @var string
     */
    protected $navId = 'unlicensed_operator';

    /**
     * @todo migrate this to use new backend query at the same time as
     * @see Olcs\Controller\Licence\LicenceController::casesAction
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

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }
}
