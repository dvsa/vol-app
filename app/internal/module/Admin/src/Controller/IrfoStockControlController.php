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
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'IrfoStockControl';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields'
            )
        )
    );

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
            ->fetchIrfoPermitStockList(
                $filters,
                [
                    'children' => [
                        'status',
                    ]
                ]
            );

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
     * Complete section and save
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processSave($data)
    {
        // IRFO stock control is NOT a standard CRUD
        // It needs to find all existing IrfoPermitStock records where
        // - serialNo is between serialNoStart and serialNoEnd (inclusive)
        // - validForYear is the one selected on the form
        // - irfoCountry is the one selected on the form
        // and update status of the record (if already exists) or create a new record

        // TODO - following functionality to be moved to its new home as part of "Separation of Business Logic"
        $irfoPermitStockService = $this->getServiceLocator()->get('Admin\Service\Data\IrfoPermitStock');

        // find all existing records
        $filters = [
            'validForYear' => $data['fields']['validForYear'],
            'irfoCountry' => $data['fields']['irfoCountry'],
            ['serialNo' => '>= ' . $data['fields']['serialNoStart']],
            ['serialNo' => '<= ' . $data['fields']['serialNoEnd']],
            'sort' => 'serialNo',
            'order' => 'ASC',
            // forms max_diff set to 100 so we should never get more than 101 records to update
            'limit' => 101,
        ];
        $results = $irfoPermitStockService->fetchIrfoPermitStockList($filters);

        // map serialNo to Index in the results table (serialNo => resultsIndex)
        $serialNoToResultsIndex = !empty($results) ? array_flip(array_column($results['Results'], 'serialNo')) : [];

        $success = true;

        for ($i = $data['fields']['serialNoStart']; $i <= $data['fields']['serialNoEnd']; $i++) {
            // set updated data
            $dataToSave = array_merge(
                isset($serialNoToResultsIndex[$i]) ?
                // use existing data
                $results['Results'][$serialNoToResultsIndex[$i]] :
                // create new
                [
                    'serialNo' => $i,
                    'validForYear' => $data['fields']['validForYear'],
                    'irfoCountry' => $data['fields']['irfoCountry'],
                ],
                // overwrite with updated status
                [
                    'status' => $data['fields']['status']
                ]
            );

            $dataObject = $irfoPermitStockService->createWithData($dataToSave);

            if (!$irfoPermitStockService->save($dataObject)) {
                // exit on the first failure
                $success = false;
                break;
            }
        }

        $this->setIsSaved(true);

        if ($success) {
            $this->addSuccessMessage('Saved successfully');
        } else {
            $this->addErrorMessage('Sorry; there was a problem. Please try again.');
        }

        return $this->redirectToIndex();
    }

    /**
     * Simple redirect to the edit form
     *
     * @return \Zend\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
            null,
            ['action' => 'index'],
            ['code' => '303'],
            true
        );
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
