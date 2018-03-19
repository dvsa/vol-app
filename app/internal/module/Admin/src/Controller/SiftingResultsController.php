<?php

/**
 * Sifting Results Controller
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\EcmtPermits\EcmtPermits as ListDto;
use Common\Controller\Traits\GenericRenderView;
use Zend\View\Model\ViewModel;
use Admin\Form\Model\Form\sectorsFilter;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Query\Sectors\Sectors;


/**
 * Sifting Results Controller
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class SiftingResultsController extends AbstractInternalController implements LeftViewProvider
{

    use GenericRenderView;

    protected $navigationId = 'admin-dashboard/admin-sifting';


    // list
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/sifting/sifting-results';
    protected $defaultTableSortField = 'permitsId';
    protected $defaultTableOrderField = 'ASC';
    protected $defaultTableLimit = 25;
    protected $tableName = 'admin-sifting-results';
    protected $listDto = ListDto::class;
    protected $filterForm = sectorsFilter::class;
    protected $defaultSector = 4;




    public function indexAction()
    {
        $tableData = $this->getTableData();
        $sectorName = $this->getSectorName();

        $this->placeholder()->setPlaceholder('pageTitle', 'Sifting Results');
        $this->placeholder()->setPlaceholder('tableHeader', $tableData . ' permit applications for sector ');
        $this->placeholder()->setPlaceholder('sectorName', $sectorName);
        return parent::indexAction();
    }

    public function getLeftView()
    {
        $view = new ViewModel(
          [
            'navigationId' => 'admin-dashboard/admin-sifting',
            'navigationTitle' => 'Sifting Results'
          ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }


    /**
     * Convert from form values to query values
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters = parent::modifyListQueryParameters($parameters);

        $urlParams = $this->getRequest()->getQuery();

        $parameters['sectorId'] = array_key_exists('sectors',$urlParams) && $urlParams['sectors']>0 ? $urlParams['sectors'] : $this->defaultSector;
        $parameters['sort'] = array_key_exists('sort',$urlParams) ? $urlParams['sort'] : $this->defaultTableSortField;
        $parameters['order'] = array_key_exists('order',$urlParams) ? $urlParams['order'] : $this->defaultTableOrderField;


        return $parameters;
    }


    /**
     * Make call to Api to get data of Table of Permits
     *
     * @return array|null
     */
    protected function getTableData()
    {
        $urlParams = $this->getRequest()->getQuery();
        $data['sectorId'] = array_key_exists('sectors',$urlParams) && $urlParams['sectors']>0 ? $urlParams['sectors'] : $this->defaultSector;
        $data['sort'] = array_key_exists('sort',$urlParams) ? $urlParams['sort'] : $this->defaultTableSortField;
        $data['order'] = array_key_exists('order',$urlParams) ? $urlParams['order'] : $this->defaultTableOrderField;
        $data['page'] = array_key_exists('page',$urlParams) ? $urlParams['page'] : '1';
        $data['limit'] = array_key_exists('limit',$urlParams) ? $urlParams['limit'] : $this->defaultTableLimit;

        $response = $this->handleQuery(ListDto::create($data));

        if ($response->isForbidden()) {
            return null;
        }
        $result = $response->getResult();

        return $result['count'];
    }

    /**
     * Get the sector name
     *
     * @return string
     */
    protected function getSectorName()
    {
        $urlParams = $this->getRequest()->getQuery();
        $data['sort'] = 'sectorId';
        $data['page'] = '1';
        $data['order'] = 'ASC';
        $data['limit'] = $this->defaultTableLimit;
        $sectorId = array_key_exists('sectors',$urlParams) && $urlParams['sectors']>0 ? $urlParams['sectors'] : $this->defaultSector;
        $response = $this->handleQuery(Sectors::create($data));
        $results = $response->getResult();

        foreach ($results['results'] as $result)
        {
            if ($result['sectorId'] == $sectorId)
            {
                return $result['sectorName'];
            }
        }

    }

}
