<?php
/**
 * IRFO Stock Control Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;

/**
 * IRFO Stock Control Controller
 */

class IrfoStockControlController extends CrudAbstract
{
    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'admin-irfo-stock-control';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-printing-section';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'IrfoPermitStock';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-printing/irfo-stock-control';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'children' => [
            'status',
        ]
    ];

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Extend the render view method
     *
     * @param string|\Zend\View\Model\ViewModel $view
     * @param string|null $pageTitle
     * @param string|null $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        if (is_null($pageTitle)) {
            $pageTitle = 'IRFO stock control';
        }

        return parent::renderView($view, $pageTitle, $pageSubTitle);
    }

    /**
     * Index Action.
     */
    public function indexAction()
    {
        $this->checkForCrudAction();

        // set defaults
        $filters = array_merge(
            [
                'validForYear' => $this->getServiceLocator()->get('Helper\Date')->getDate('Y'),
                'page' => 1,
                'sort' => 'serialNo',
                'order' => 'ASC',
                'limit' => 25
            ],
            $this->getRequest()->getQuery()->toArray()
        );

        if (empty($filters['irfoCountry'])) {
            // if not yet set, set default irfoCountry to the first country on the list
            $irfoCountries = $this->getServiceLocator()->get('Olcs\Service\Data\IrfoCountry')
                ->fetchListData();
            if (!empty($irfoCountries)) {
                $filters['irfoCountry'] = $irfoCountries[0]['id'];
            }
        }

        // get filtered data
        $results = $this->getServiceLocator()->get('Admin\Service\Data\IrfoPermitStock')
            ->fetchIrfoPermitStockList($filters);

        // set table
        $table = $this->getTable(
            'admin-irfo-stock-control',
            !empty($results) ? $results : [],
            array_merge(
                $filters,
                array('query' => $this->getRequest()->getQuery())
            )
        );

        // set table filter form
        $form = $this->getForm('IrfoStockControlFilter');
        $form->remove('csrf'); //we never post
        $form->setData($filters);
        $this->setTableFilters($form);

        // render table view
        $view = $this->getView(['table' => $table]);
        $view->setTemplate('partials/table');

        return $this->renderView($view);
    }

    /**
     * Sets the table filters.
     *
     * @param mixed $filters
     */
    public function setTableFilters($filters)
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')->set($filters);
    }
}
