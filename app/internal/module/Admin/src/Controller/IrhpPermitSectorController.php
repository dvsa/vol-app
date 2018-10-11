<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Command\IrhpPermitSector\Update as Update;
use Dvsa\Olcs\Transfer\Query\IrhpPermitSector\GetList as ListDto;
use Admin\Data\Mapper\IrhpPermitSector as PermitSectorMapper;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;

/**
 * IRHP Permits Sector controller
 */
class IrhpPermitSectorController extends AbstractInternalController implements LeftViewProvider
{

    protected $tableName = 'admin-irhp-permit-sector';

    protected $listVars = ['irhpPermitStock' => 'parentId'];
    protected $listDto = ListDto::class;
    protected $mapperClass = PermitSectorMapper::class;

    protected $indexPageTitle = 'Permits';

    protected $tableViewTemplate = 'pages/irhp-permit-sector/index';

    protected $parentEntity = 'irhpPermitStock';

    protected $navigationId = 'admin-dashboard/admin-permits';

    protected $defaultData = ['parentId' => 'route'];

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => 'Permits'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Sector Quota Index Action
     *
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        $this->getServiceLocator()->get('Script')->loadFile('irhp-permit-sector');

        $request = $this->getRequest();

        //Handle incoming POST request
        if ($request->isPost()) {
            $postParams = $this->params()->fromPost();

            /**
             * If the POST action is 'cancel', then navigate the user back to the Permit System Settings page.
             * Otherwise, save the current Sector Quota values in the databse.
             */
            if ($postParams['action'] == 'Cancel') {
                $this->redirect()->toRoute($this->navigationId . '/permits-system-settings');
            } else {
                $parentId = $this->params()->fromRoute();
                $sectors = $postParams['sectors'];

                $cmdData = [
                    'irhpPermitStock' => (int) $parentId,
                    'sectors' => $sectors
                ];

                $response = $this->handleCommand(Update::create($cmdData));

                if (!$response->isOk()) {
                    $this->handleErrors($response->getResult());
                }
            }
        }

        return parent::indexAction();
    }
}
