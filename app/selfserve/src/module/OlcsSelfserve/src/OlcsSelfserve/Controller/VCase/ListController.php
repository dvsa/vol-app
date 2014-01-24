<?php

/**
 * List of Cases.
 *
 * OLCS-9
 *
 * @package		olcs
 * @subpackage	VCase
 * @author		Mike Cooper
 */

namespace Olcs\Controller\VCase;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;       // <-- Add this import

class ListController extends AbstractActionController
{

    public $messages = null;

    protected $listWrapper= 'olcs/common/generic-list/genericListWrapperNoFilter';

    protected $listTemplate = 'olcs/vcase/list/caseList';

    protected $listThead = 'olcs/vcase/list/caseListThead';

    /*
     * Generates a case list using the DataListPlugin
     */
    public function indexAction()
    {
        $this->route =  $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $licenceId = (int) $this->getEvent()->getRouteMatch()->getParam('licenceId');

        if (!empty($licenceId)) {
            $licence = $this->service('Olcs\Licence')->get(intval($licenceId));
        }
        if (empty($licence)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $dataList = $this->DataListPlugin();
        if ($this->getRequest()->getQuery('column'))
        $dataList->setTableHeaderParams(array('sortColumn' => $this->getRequest()->getQuery('column'),
                                                                            'sortDir' => $this->getRequest()->getQuery('dir')));
        $dataList->setListWrapper($this->listWrapper)
                        ->setAjaxUrl('/case/list/'.$licenceId.'/page/1/'.($this->getRequest()->getQuery('s') ? $this->getRequest()->getQuery('s') : '10'))
                        ->setListHeader('case-associated-with')
                        ->setListHeaderParams(array('@operatorName' => $licence['operator']['operatorName']))
                        ->setPartials(array('topLeftButton' => 
                                                        array('view' =>'olcs/vcase/list/createNewCaseButton.phtml', 'params' => 
                                                                array('licenceId' => $licence['licenceId']))))
                        ->setListPaging('olcs/common/generic-list/listPageBarQueryString');
        $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
        $paginated = $paginator->getPaginatedResults($this->getRequest()->getQuery(), $this);
        $view = $dataList->createList($paginated, $paginated, $this->listTemplate, $this->listThead);

        return $view;
    }

    /*
     * Gets the data for the list
     */
    public function getListData($direction = null, $column = null, $limit, $offset)
    {
        $licenceId = (int) $this->getEvent()->getRouteMatch()->getParam('licenceId');
        if ( $this->getRequest()->getQuery("dir") ) {
            $direction=$this->getRequest()->getQuery("dir");
        }
        if ( $this->getRequest()->getQuery("column") ) {
            $column=$this->getRequest()->getQuery("column");
        }
        if (!empty($licenceId)) {
            $cases = $this->service('Olcs\Licence')->get($licenceId . '/cases', array_filter(array(
                'sortColumn' => $column,
                'sortReversed' => ($direction == 'dn'),
                'limit' => $limit,
                'offset' => $offset,
            )));
        }
        return !isset($cases['rows']) 
            ? array('listCount' => 0,
                            'listData' => array()) 
            : array('listCount' => $cases['count'],
                            'listData' => $cases['rows'],
        );
    }

    public function ajaxCaseListAction() 
    {
        $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
        $numPerPage = $this->params()->fromQuery('s') ? $this->params()->fromQuery('s') : $paginator->getNumPerPage();
        $currentPage = $this->params()->fromQuery('page') ? $this->params()->fromQuery('page') : 1;
        $listData = $this->getListData(
            $this->getRequest()->getQuery('dir'),
            $this->getRequest()->getQuery('column'),
            $numPerPage,
            $paginator->getOffset($currentPage, $paginator->getNumPerPage())
        );
        $listView = new ViewModel(array('listData' => $listData['listData']));
        $listView->setTemplate($this->listTemplate);

        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($listView); 
        return $this->getResponse()->setContent($html);
    }
}
