<?php

/**
 * Transport Manager Case Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\TransportManager\TransportManagerController;

/**
 * Transport Manager Case Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerCaseController extends TransportManagerController
{
    /**
     * @var string
     */
    protected $section = 'cases';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTm();

        $this->checkForCrudAction('case', [], 'case');

        $view = $this->getViewWithTm();

        $params = [
            'transportManager' => $this->params()->fromRoute('transportManager', null),
            'page'    => $this->params()->fromRoute('page', 1),
            'sort'    => $this->params()->fromRoute('sort', 'id'),
            'order'   => $this->params()->fromRoute('order', 'desc'),
            'limit'   => $this->params()->fromRoute('limit', 10),
        ];

        $results = $this->getServiceLocator()
                        ->get('DataServiceManager')
                        ->get('Olcs\Service\Data\Cases')->fetchList($params);

        $view->{'table'} = $this->getTable('case', $results, $params);

        $view->setTemplate('partials/table');

        return $this->renderView($view);
    }
}
