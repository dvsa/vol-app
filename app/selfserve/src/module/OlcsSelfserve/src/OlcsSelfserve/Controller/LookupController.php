<?php
/**
 * Basic Lookup.
 *
 * OLCS-5
 * 
 * @package		olcs
 * @subpackage	lookup
 * @author		Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace Olcs\Controller;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;       // <-- Add this import
use Zend\Validator\NotEmpty;
use Olcs\Form\FormEmptyValidator;


class LookupController extends AbstractActionController
{
    public $messages = null;

    protected $listWrapper= 'olcs/common/generic-list/genericListWrapperNoFilter';

    protected $listTemplate = 'olcs/lookup/operator-list/operatorList';

    protected $listThead = 'olcs/lookup/operator-list/operatorListThead';
    
    public function indexAction()
    {
        $searchForm = new Form\SearchForm();
        
        // Retrieve the HTTP request. If this is POST we assume it is a posted form
        // and make the decision where to redirect to based on the rules in OLCS-5
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $postParams = $this->getRequest()->getPost()->toArray();
            unset($postParams['submit']);
            $filteredPostParams = array_filter($postParams, "self::buildQueryStringArray");
            $validator = new FormEmptyValidator();
            $valueFound = $validator->isValid($postParams);
            $getVariables=http_build_query($filteredPostParams);
                if ($valueFound) {
                    $transId = $this->getRequest()->getPost('transportManagerId');
                    if (!empty($transId)) {
                        return $this->redirect()->toUrl('/search/person-results?'.$getVariables);
                    }
                    // if firstname or lastname or full DoB present
                    if (( $this->getRequest()->getPost('firstName') != "" )
                            || ($this->getRequest()->getPost('lastName') != "" )
                            || (($this->getRequest()->getPost('dobDay') != "" )
                            && ($this->getRequest()->getPost('dobMonth') != "" )
                            && ($this->getRequest()->getPost('dobYear') != "" ))) {
                            return $this->redirect()->toUrl('/search/person-results?'.$getVariables);
                    } else {
                            return $this->redirect()->toRoute('operator_results', array('page'=>1,'s'=>10), array('query'=>$filteredPostParams));
                    }
                } else {
                    $this->messages = array('Enter at least one search criteria before Searching.');
                }
        }

        $view = new ViewModel(array('searchForm' => $searchForm, 'messages' => $this->messages));
        $view->setTemplate('olcs/lookup/lookupWrapper');
        
        $basicLookupView = new ViewModel(array('basicLookupForm' => $searchForm));
        $basicLookupView->setTemplate('olcs/lookup/basic');
        
        $advancedLookupView = new ViewModel(array('advancedLookupForm' => $searchForm));
        $advancedLookupView->setTemplate('olcs/lookup/advanced');
        
        $view->addChild($basicLookupView, 'basicLookupView')
                    ->addChild($advancedLookupView, 'advancedLookupView');
        
        return $view;
        
    }
    
    /*
     * Used by array_filter to remove any empty params from the search form
     */
    private function buildQueryStringArray($param) {
        if (!empty($param)) return true;
    }

    /*
     * Returns the operator list view with pagination and filtering
     */
    public function operatorResultsAction()    {
        
        $this->route =  $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $filters = include (__DIR__ . '/../../../config/filters.config.php');
        $dataList = $this->DataListPlugin();
        $filterQueryParams = array();
        $queryParams = array();
        $getParams = $this->getRequest()->getQuery()->toArray();
        unset($getParams['open']);
        foreach($getParams as $key => $param) {
                if (preg_match('/^lf/', $key)) {
                    $filterQueryParams[] = $param;
                } else {
                    $queryParams[$key] = $param;
                }
        }
        $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
        $paginated = $paginator->getPaginatedResults($this->getRequest()->getQuery(), $this);
        $filterUrl =  '/search/operator-results/page/1/'.$this->getEvent()->getRouteMatch()->getParam('s');
        if ($this->getRequest()->getQuery('column'))  
            $dataList->setTableHeaderParams(array('sortColumn' => $this->getRequest()->getQuery('column'),
                                                                                'sortDir' => $this->getRequest()->getQuery('dir')));
        $dataList->setAjaxUrl('/search/operator-results/page/1/'.$this->getEvent()->getRouteMatch()->getParam('s'))
                        ->setPartials(array('left-filters' =>
                                                array('view' =>'olcs/lookup/operator-list/leftFilters.phtml',
                                                            'params' => array('filters' => $filters,
                                                            'filterQueryParams' => $filterQueryParams,
                                                            'filterOpen' => $this->getRequest()->getQuery('open') ? $this->getRequest()->getQuery('open') : '00001',
                                                            'baseQueryString' => http_build_query($queryParams),
                                                            'baseUrl' =>  $filterUrl))))
                        ->setListPaging('olcs/common/generic-list/listPageBarQueryString');

        $view = $dataList->createList($paginated, $paginated, $this->listTemplate, $this->listThead);

        return $view;
    }

    /*
     * Gets the data for the list
     */
    public function getListData($direction=null, $column=null, $numPerPage, $offset)
    {
        if ( $this->getRequest()->getQuery("dir") ) {
            $direction=$this->getRequest()->getQuery("dir");
        }
        $sortReversed = ($direction == 'dn') ? true : false;

        if ( $this->getRequest()->getQuery("column") ) {
            $column=$this->getRequest()->getQuery("column");
        }

        $results = $this->service('Olcs\Lookup')->get(array_filter(array(
            'type' => 'licence',
            'sortColumn' => $column,
            'sortReversed' => $sortReversed,
            'offset' => $offset,
            'limit' => $numPerPage,
            'search' => array_filter($this->getRequest()->getQuery()->toArray()),
        )));
        $results =  empty($results) ? array() : (object)array(
            'listCount' => $results['count'],
            'listData' => $results['rows'],
        );

        return $results;
    }

    public function ajaxOperaterResultsAction() {
        
        $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
        $numPerPage = $this->params()->fromQuery('s') ? $this->params()->fromQuery('s') : $paginator->getNumPerPage();
        $currentPage = $this->params()->fromQuery('page') ? $this->params()->fromQuery('page') : 1;
        $listData = $this->getListData($this->getRequest()->getQuery("dir") ? $this->getRequest()->getQuery("dir") : null,
                                        $this->getRequest()->getQuery("column") ? $this->getRequest()->getQuery("column")  : null,
                                        $numPerPage,
                                        $paginator->getOffset($currentPage, $numPerPage)
                                        );
        $listView = new ViewModel(array('listData' => $listData->listData));
        $listView->setTemplate($this->listTemplate);

        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($listView); 
        return $this->getResponse()->setContent($html);
        
    }
    
    /**
    * Action for 'Search Person'
    * @return	\Zend\View\Model\ViewModel		View model for this render
    */
    public function personResultsAction()    {
         
        $request = $this->getRequest();
        $searchArray=Array();
        if ( $request->getQuery()->firstName != "" ) {
            $searchArray['firstName']=$request->getQuery()->firstName;
        }
        if ( $request->getQuery()->lastName != "" ) {
            $searchArray['lastName']=$request->getQuery()->lastName;
        }
        if (( $request->getQuery()->dobDay != "" )
            && ( $request->getQuery()->dobMonth != "" )
            && ( $request->getQuery()->dobYear != "" )) {
            $searchArray['dateOfBirth']=sprintf("%04d-%02d-%02d",
            $request->getQuery()->dobYear,
            $request->getQuery()->dobMonth,
            $request->getQuery()->dobDay);
        }

        $query = array(
            'type' => 'person',
            'search' => $searchArray,
        );
        $data = $this->service('Olcs\Lookup')->get($query)['rows'];
        $view = new ViewModel(array('persons' => $data));
        $view->setTemplate('olcs/lookup/person-results');
        return $view;		
    }
    
}
