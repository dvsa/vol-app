<?php

/**
 * Plugin to create a generic data list with sorting and paging
 *
 * @author Mike Cooper
 */

namespace Olcs\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;

class DataListPlugin  extends AbstractPlugin
{
    
    /*
     * List wrapper to use. Defaults to the one with filters as this was the first one done.
     */
    private $listWrapper = 'olcs/common/generic-list/genericListWrapper';
    /*
     * List template to default to
     */
    private $listTemplate = 'olcs/common/generic-list/genericListTemplate';
    /*
     * List header template to default to
     */
    private $listHeaderTemplate = 'olcs/common/generic-list/genericListHeader';
    /*
     * Resource string for the list header
     */
    private $listHeader;
    /*
     * List paging template 
     */
    private $listPaging = 'olcs/common/generic-list/listPageBar';
    /*
     * Values for any attributes on the table wrapper
     */
    private $listWrapperAttributes = array();
    /*
     * Values for any params in the header 
     */
    private $listHeaderParams = array();
    /*
     * Values for anyparams in the table header 
     */
    private $tableHeaderParams = array();
    /*
     * Values for any params in the paging view
     */
    private $pagingParams = array();
    /*
     * Sorting ajax url
     */
    private $ajaxUrl;
    /*
     * Any partials to include in the list
     */
    private $partials;
    
    /*
     * Creates list with paging and sorting
     * 
     * @param (listData) data for list view
    *  @param (paging) data for paging view
     * @param (list) the list table view template
     * @param (listHeader) the list table thead view template
     * @return a rendered list view
     */
    public function createList($listData, $paging = null, $list = null, $listHeader = null)
    {
        // To remove any sorting params in the query string
        if (array_key_exists('dir', $_GET)) {
            unset($_GET['dir']);
        }
        if (array_key_exists('column', $_GET)) {
            unset($_GET['column']);
        }
        $getVariables=http_build_query($_GET);
        
        $viewParams = array(
            'tableAttributes' => $this->listWrapperAttributes,
            'baseUrl' => $getVariables,
            'listHeader' => $this->listHeader,
            'headerParams' => $this->listHeaderParams,
            'ajaxUrl' => $this->ajaxUrl,
            'partials' => $this->getPartials(),
        );
        
        $view = new ViewModel($viewParams);

        $view->setTemplate($this->listWrapper);

        $listData = (is_array($listData) && isset($listData['rows'])) ? $listData : array('listData' => $listData);

        $listView = new ViewModel($listData);
        $listView->setTemplate(empty($list) ? $this->listTemplate : $list);
        $view->addChild($listView, 'listView');
            
        if ($listHeader || $this->tableHeaderParams) {
            $listHeaderView = new ViewModel(array('tableHeaderParams' => $this->tableHeaderParams));
            $listHeaderView->setTemplate(empty($listHeader) ? $this->listHeaderTemplate : $listHeader);
            $view->addChild($listHeaderView, 'listHeaderView');
        }

        if ($paging) {
            // Add any custom params to the paging params.
            $this->pagingParams = $this->pagingParams+array('paginator' => $paging);
            $pagingView = new ViewModel($this->pagingParams);
            $pagingView->setTemplate($this->listPaging);
            $view->addChild($pagingView, 'pagingView');
        }

        return $view;
    }
    
    public function getListWrapper()
    {
        
    }
    
    public function setListWrapper($wrapper)
    {
        $this->listWrapper = $wrapper;
        return $this;
    }
    
    public function setListHeader($header)
    {
        $this->listHeader = $header;
        return $this;
    }
    
    public function setListHeaderParams(array $params=array())
    {
        $this->listHeaderParams = $params;
        return $this;
    }

    public function setListWrapperAttributes(array $params = array())
    {
        $this->listWrapperAttributes = $params;
        return $this;
    }

    public function setListPaging($listPaging)
    {
        $this->listPaging = $listPaging;
        return $this;
    }
    
    public function setAjaxUrl($url)
    {
        $this->ajaxUrl = $url;
        return $this;
    }
    
     /*
     * Set partials to be used within the view wrapper like "topRightButton" etc.
     */
    public function setPartials(array $partials = array())
    {
        $this->partials = $partials;
        return $this;
    }
   
    public function getPartials()
    {
        return $this->partials;
    }
    
    /*
     * Set any params for the list header view
     */
    public function setTableHeaderParams(array $tableHeaderParams)
    {
        $this->tableHeaderParams = array_merge($this->tableHeaderParams, $tableHeaderParams);
        return $this;
    }
    
    /*
     * Set any params for the list paging view
     */
    public function setPagingParams(array $pagingParams)
    {
        $this->pagingParams = array_merge($this->pagingParams, $pagingParams);
    }
    
}
