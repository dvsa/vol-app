<?php

/**
 * IRHP Permit Application Controller
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Olcs\Controller\IrhpPermits;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetList as CandidateListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\ById as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpPermitApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

class IrhpPermitController extends AbstractInternalController implements
    IrhpPermitApplicationControllerInterface,
    LeftViewProvider
{

    protected $itemParams = ['id' => 'irhppermitid'];
    protected $deleteParams = ['id' => 'irhppermitid'];

    protected $tableName = 'irhp-permits';
    protected $defaultTableSortField = 'permitNumber';
    protected $defaultTableOrderField = 'DESC';

    protected $listVars = ['irhpPermitApplication' => 'permitid'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;

    protected $hasMultiDelete = false;
    protected $indexPageTitle = 'IRHP Permits';

    // After Adding and Editing we want users taken back to index dashboard
    protected $redirectConfig = [];

    /**
     * Get left view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'irhp_permits',
                'navigationTitle' => 'Application details'
            ]
        );

        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $response = $this->handleQuery(ListDTO::create([
            'page' => 1,
            'sort' => 'id',
            'order' => 'ASC',
            'limit' => 10,
            'irhpPermitApplication' => $this->params()->fromRoute('permitid')
        ]));

        if ($response->getResult()['count'] === 0) {
            $this->listDto = CandidateListDTO::class;
            $this->tableName = 'candidate-permits';
            $this->defaultTableSortField = 'id';
            $this->defaultTableOrderField = 'ASC';
            $this->listVars = ['ecmtPermitApplication' => 'permitid'];
        }

        return parent::indexAction();
    }
}
