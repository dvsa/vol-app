<?php

/**
 * Unlicensed Operator Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Operator;

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
        $licences = $this->getServiceLocator()->get('Entity\Organisation')->getLicencesByStatus(
            $this->getQueryOrRouteParam('organisation'),
            [\Common\RefData::LICENCE_STATUS_UNLICENSED]
        );

        $licenceId = array_shift($licences)['id'];

        $this->checkForCrudAction('case', ['licence' => $licenceId], 'case');
        $view = $this->getViewWithOrganisation();

        $params = [
            'licence' => $licenceId,
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'createdOn'),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
            'query'   => $this->getRequest()->getQuery()->toArray(),
        ];

        $bundle = array(
            'children' => array(
                'caseType' => array()
            )
        );

        $results = $this->makeRestCall('Cases', 'GET', $params, $bundle);

        $view->{'table'} = $this->getTable('cases', $results, $params);

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }
}
