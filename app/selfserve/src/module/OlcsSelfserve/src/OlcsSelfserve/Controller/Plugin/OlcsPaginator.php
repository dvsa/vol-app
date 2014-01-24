<?php

/*
 * Extends Zend Paginator. Takes in a list object and returns a paginator object to be
 * used in list pagination.
 *
 * @author Mike Cooper
 */

namespace Olcs\Controller\Plugin;

use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Paginator\Adapter\Collection as Adapter;
use Zend\Paginator\Paginator;

class OlcsPaginator extends \Zend\Paginator\Paginator {
    
    public $paginator;
    
    private $numPerPage = 10;
    
    public function __construct() {
        //parent::__construct($adapter);
    }
    
    public function getPaginator($data, $currentPage=1, $pageRange=5, $itemsPerPage=null) 
    {
        $data = (object)$data;
        if (!empty($itemsPerPage)) $this->numPerPage = $itemsPerPage;
        $collection = new ArrayCollection($data->listData);
        $this->paginator = new Paginator(new Adapter($collection));
        $this->paginator->setCurrentPageNumber(1)
                                    ->setItemCountPerPage($this->numPerPage)
                                    ->setPageRange(5);
        $this->paginator->realCurrentPage = $currentPage; // Sets the current page to the page passed in the url
        $this->paginator->pageCount = ceil($data->listCount / $this->numPerPage);
        $this->paginator->totalCount = $data->listCount;
        return $this->paginator;
    }

    public function getPageDetails($controller)
    {
        $routeParams = $controller->getEvent()->getRouteMatch()->getParams();
        if (isset($routeParams['s'])) {
            $this->setNumPerPage($routeParams['s']);
        }
        $currentPage = isset($routeParams['page']) ? $routeParams['page'] : 1;

        $limit = $this->getNumPerPage();

        return array(
            'offset' => $this->getOffset($currentPage, $limit),
            'limit' => $limit,
            'page' => $currentPage,
        );
    }

    public function createPaginator($controller, $data, $currentPage, $route)
    {
        $paginated = $this->getPaginator($data, $currentPage);
        $paginated->route = $route;
        $paginated->routeParams = $controller->getEvent()->getRouteMatch()->getParams();
        $paginated->queryString = $controller->getRequest()->getQuery()->toArray();
        return $paginated;
    }

    public function getPaginatedResults($params, $controller)
    {
        $pageDetails = $this->getPageDetails($controller);

        $data = $controller->getListData(null, null, $pageDetails['limit'], $pageDetails['offset']);

        return $this->createPaginator($controller, $data, $pageDetails['page'], $controller->route);
    }
    
    public function GetOffset($currentPage, $numPerPage=null) 
    {
        if (!empty($numPerPage)) $this->numPerPage = $numPerPage;
        return $currentPage == 1 ? 0 : (($currentPage - 1) * $this->numPerPage);
    }
    
    public function getNumPerPage() 
    {
        return $this->numPerPage;
    }
    
    public function setNumPerPage($numPerPage) 
    {
        return $this->numPerPage = $numPerPage;
    }
    
    public function setThisPage($thisPage) 
    {
        return $this->thisPage = $numPerPage;
    }
    
}

?>
