<?php

/**
 * Case Details Form.
 *
 * This is the controller for the form that adds details to a case. The second
 * step of the "wizard"-like flow that's part of the creation of a case, the
 * first step being controlled by Olcs\Controller\VCase\New.
 *
 * @package		olcs
 * @subpackage	vcase
 * @author		Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\VCase;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json;
use Zend\View\Model\JsonModel;

class ConvictionsController extends AbstractActionController
{
    
    protected $listWrapper= 'olcs/common/generic-list/genericListWrapperPlain';
    
    protected $listTemplate = 'olcs/vcase/convictions/convictionsList';
    
    protected $listThead = 'olcs/vcase/convictions/convictionsListThead';
    
    public function indexAction()
    {
        $this->getRequest()->getQuery()->set('licenceId', $this->getEvent()->getRouteMatch()->getParam('licenceId'));
        $this->getRequest()->getQuery()->set('caseId', $this->getEvent()->getRouteMatch()->getParam('caseId'));
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'list of cases');
        $page->setParams(array('licenceId' =>  $this->getEvent()->getRouteMatch()->getParam('licenceId')));
        
        $request = $this->getRequest();
        $params = $this->getPageParams($request->getQuery('caseId'));

        $actionForm = new \Olcs\Form\VCase\ActionForm($params['caseId']);
        $actionForm->get('licenceId')->setValue($this->getEvent()->getRouteMatch()->getParam('licenceId'));
        $form = new \Olcs\Form\VCase\DetailsForm();
        foreach ($actionForm->getElements() as $element) {
            $form->add($element);
        }

        if ($request->isPost()) {
            // save here
            $this->updateComment((int)$request->getPost('caseId'), $request->getPost()->toArray());

            // if set to go to generate submission, forward else don't.
            $action = $actionForm->handleAction($request->getPost('submitActionTypes'), $this);
            if ($action) {
                return $action;
            } else {
                return $this->redirect()->toUrl('/case/dashboard?' . http_build_query(array(
                    'caseId' => $request->getPost('caseId')
                )));
            }
        }
        
        if (!$params) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $form->get('caseId')->setValue($params['caseId']);
        
        if (!empty($params['detailCommentId'])) {
            $form->get('commentId')->setValue($params['detailCommentId']);
        }
        if (!empty($params['detailComment'])) {
            $form->get('caseDetailsNote')->setValue($params['detailComment']);
        }

        $dataList = $this->DataListPlugin();
        $dataList->setListPaging('olcs/common/generic-list/listPageBarBlank');
        $dataList->setListWrapper($this->listWrapper)
                 ->setAjaxUrl('/case/ajax-conviction-list-sort')
                 ->setListHeader('case-associated-with');

        $data = $this->getListData();
        $dataListView = $dataList->createList($data, null, $this->listTemplate, $this->listThead);
        
        $view = new ViewModel(array('params' => $params,
                                    'detailsForm' => $form,
                                    'caseId' => $request->getQuery('caseId'),
                                    'licenceId' => $this->getRequest()->getQuery('licenceId')));
        $view->setTemplate('olcs/vcase/convictions/details');
        $view->addChild($dataListView, 'dataListView');

        return $view;
    }
    
    public function formPostAction() 
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            // save here
            $this->updateComment((int)$request->getPost('caseId'), $request->getPost()->toArray());

            // if set to go to generate submission, forward else don't.
            $actionForm = new \Olcs\Form\VCase\ActionForm((int)$request->getPost('caseId'));
            $action = $actionForm->handleAction($request->getPost('submitActionTypes'), $this);
            if ($action) {
                return $action;
            } else {
                return $this->redirect()->toUrl('/case/'.intval($request->getPost('licenceId')).'/'.intval($request->getPost('caseId')).'/dashboard');
            }
        }
    }
    
    /*
     * Saves comment from the save button via ajax
     */
    public function ajaxDetailSaveAction() 
    {
        $params = $this->getRequest()->getPost()->toArray();
        $commentId = $this->updateComment((int) $params['caseId'], $params);

        if (!$commentId) {
            $response = array('status' => 'failed');
        } else if ($this->getRequest()->getPost('commentId')) {
            $response = array('status' => 'updated', 'commentId' => $commentId);
        } else {
            $response = array('status' => "created", 'commentId' => $commentId);
        }

        return new JsonModel($response);
        
    }

    /**
     * Fetches and assembles the data needed in the page.
     * @param int $caseId
     * @return array|false
     */
    private function getPageParams($caseId)
    {
        $caseId = intval($caseId);

        if (empty($caseId)) {
            return false;
        }

        $caseService = $this->service('Olcs\Case');
        $params = $caseService->get($caseId);

        if ($params) {
            $convictionComment = $caseService->get($caseId . '/detail-comment');
            if ($convictionComment) {
                $params = array_merge($convictionComment, $params);
            }
        }

        return $params;
    }

    /**
     * Update a case comment
     *
     * @param   int   $caseId The id of the comment's case
     * @param   array $params Parameters to save
     * @return  int|false     Comment id or false if no success
     */
    private function updateComment($caseId, array $params)
    {
        $caseId = intval($caseId);
        if (!empty($caseId)) {
            $result = $this->service('Olcs\Case')->put($caseId . '/detail-comment', $params);
        }
        return empty($result) ? false : $result['commentId'];
    }
    
    /*
     * Gets the data for the list
     */
    private function getListData($direction = null, $column = null)
    {
        $caseId = (int)$this->getRequest()->getQuery('caseId');
        $convictions = empty($caseId) ? array() : $this->service('Olcs\Case')->get($caseId . '/convictions', array_filter(array(
            'sortColumn' => $column,
            'sortReversed' => ($direction == 'dn'),
        )))['rows'];
        return $convictions;
    }
    
    public function ajaxConvictionListSortAction()
    {
        $listData = $this->getListData($this->getRequest()->getQuery('dir'), $this->getRequest()->getQuery('column'));
        $listView = new ViewModel(array('listData' => $listData));
        $listView->setTemplate($this->listTemplate);
                    
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($listView); 
        return $this->getResponse()->setContent($html);
    }
 }
