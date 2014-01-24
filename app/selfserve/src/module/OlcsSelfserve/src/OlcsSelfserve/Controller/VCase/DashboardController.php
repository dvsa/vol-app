<?php

/**
 * Dashboard of Cases.
 *
 * @package     olcs
 * @subpackage  VCase
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\VCase;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DashboardController extends AbstractActionController
{

    protected $listWrapper= 'olcs/common/generic-list/genericListWrapperPlain';
    
    protected $listTemplate = 'olcs/vcase/dashboard/submissionList';
    
    protected $listThead = 'olcs/vcase/dashboard/submissionListThead';

    /**
     * Generates a case list using the DataListPlugin
     */
    public function indexAction()
    {
        $this->getRequest()->getQuery()->set('licenceId', $this->getEvent()->getRouteMatch()->getParam('licenceId'));
        $this->getRequest()->getQuery()->set('caseId', $this->getEvent()->getRouteMatch()->getParam('caseId'));
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'list of cases');
        $page->setParams(array('licenceId' =>  $this->getEvent()->getRouteMatch()->getParam('licenceId'), 'page' => 1));
        
        $caseId = (int)$this->getRequest()->getQuery('caseId');

        if (!empty($caseId)) {
            $vcase = $this->service('Olcs\Case')->get($caseId);
        }
        if (empty($vcase)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $currentURL = '/case/dashboard?' . $this->getRequest()->getQuery()->toString();
        $actionForm = new \Olcs\Form\VCase\ActionForm($caseId, $currentURL);
        $actionForm->get('licenceId')->setValue($this->getRequest()->getQuery('licenceId'));

        $dataList = $this->DataListPlugin();
        $dataList->setListPaging('olcs/common/generic-list/listPageBarBlank');
        $dataList->setListWrapper($this->listWrapper)
                 ->setAjaxUrl('/case/ajax-case-dashboard')
                 ->setListHeader('case-associated-with');

        $data = $this->getListData($caseId);
        $dataListView = $dataList->createList($data, null, $this->listTemplate, $this->listThead);

        $view = new ViewModel(array(
            'params' => $vcase,
            'caseId' => $this->getRequest()->getQuery('caseId'),
            'licenceId' => $this->getRequest()->getQuery('licenceId'),
            'actionForm' => $actionForm,
        ));
        $view->setTemplate('olcs/vcase/dashboard/dashboard');
        
        $view->addChild($dataListView, 'dataListView');

        return $view;
    }
    
    public function formPostAction() 
    {
        $vcase = $this->service('Olcs\Case')->get((int)$this->getRequest()->getQuery('caseId'));

        if (empty($vcase)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $currentURL = '/case/dashboard?' . $this->getRequest()->getQuery()->toString();
        $actionForm = new \Olcs\Form\VCase\ActionForm($vcase['caseId'], $currentURL);
        if ($this->getRequest()->isPost()) {
            $action = $actionForm->handleAction($this->getRequest()->getPost('submitActionTypes'), $this);
            if ($action) {
                return $action;
            } else {
                print 'got here';
                return $this->redirect()->toUrl($currentURL);
            }
        }
    }

    /*
     * Gets the data for the list
     */
    private function getListData($caseId, $direction = null, $column = null)
    {
        $caseId = (int)$caseId;
        $submissions = empty($caseId) ? array() : $this->service('Olcs\Case')->get($caseId . '/submissions', array_filter(array(
            'sortColumn' => $column,
            'sortReversed' => ($direction == 'dn'),
        )))['rows'];
        return $submissions;
    }

    public function ajaxCaseDashboardAction() 
    {
        $listData = $this->getListData($this->getRequest()->getQuery('caseId'), $this->getRequest()->getQuery('dir'), $this->getRequest()->getQuery('column'));
        $listView = new ViewModel(array('listData' => $listData));
        $listView->setTemplate($this->listTemplate);
                    
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($listView); 
        return $this->getResponse()->setContent($html);
    }

}
