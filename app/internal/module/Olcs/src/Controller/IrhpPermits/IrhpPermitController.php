<?php

/**
 * IRHP Permit Application Controller
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Olcs\Controller\IrhpPermits;

use Common\RefData;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByEcmtId as ListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByIrhpId as IrhpListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Permits\ValidEcmtPermits as ValidEcmtPermitsDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Replace as ReplaceDTO;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Terminate as TerminateDTO;
use Dvsa\Olcs\Transfer\Command\Permits\ExpireEcmtPermitApplication as ExpireDTO;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpPermitApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\IrhpPermit as IrhpPermitMapper;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetList as CandidateListDTO;
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

    protected $listVars = ['ecmtPermitApplication' => 'permitid'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;

    protected $hasMultiDelete = false;
    protected $indexPageTitle = 'IRHP Permits';

    // After Adding and Editing we want users taken back to index dashboard
    protected $redirectConfig = [];

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'indexAction' => ['table-actions']
    );

    protected $totalPermits = 0;

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
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postParams = $this->params()->fromPost();
            if (isset($postParams['action'])) {
                if ($postParams['action'] === 'Request Replacement') {
                    return $this->redirect()->toRoute(
                        'licence/irhp-permits',
                        [
                            'action'       => 'requestReplacement',
                            'irhpPermitId' => $postParams['id']
                        ],
                        ['query' => ['irhpPermitId' => $postParams['id']]],
                        true
                    );
                }
                if ($postParams['action'] === 'Terminate') {
                    return $this->redirect()->toRoute(
                        'licence/irhp-permits',
                        [
                            'action'       => 'terminatePermit',
                            'irhpPermitId' => $postParams['id']
                        ],
                        ['query' => ['irhpPermitId' => $postParams['id']]],
                        true
                    );
                }
            }
        }

        // Get Permit Type from route, switch relevant class variables for ecmt/irhp DTOs
        $permitTypeId = intval($this->params()->fromRoute('permitTypeId'));
        if ($permitTypeId !== RefData::ECMT_PERMIT_TYPE_ID) {
            $this->listDto = IrhpListDTO::class;
            $this->listVars = ['irhpApplication' => 'permitid'];
            $this->tableName = 'irhp-bilateral-permits';
        }

        $DTOApplicationKey = $permitTypeId === RefData::ECMT_PERMIT_TYPE_ID ? 'ecmtPermitApplication' : 'irhpApplication';
        $response = $this->handleQuery($this->listDto::create([
            'page' => 1,
            'sort' => 'id',
            'order' => 'ASC',
            'limit' => 10,
            $DTOApplicationKey => $this->params()->fromRoute('permitid'),
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

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function requestReplacementAction()
    {
        if ($this->getRequest()->isPost()) {
            // If post handle suceeds, redirect to index, else re-render in modal to show errors.
            if ($this->handleReplacementPost()) {
                return $this->redirect()->toRouteAjax(
                    'licence/irhp-permits',
                    [
                        'action' => 'index',
                        'licence' => $this->params()->fromRoute('licence'),
                        'permitid' => $this->params()->fromRoute('permitid')
                    ]
                );
            }
        }

        $replacingId = $this->params()->fromQuery('irhpPermitId');
        $irhpPermit = $this->handleQuery(ItemDTO::create(['id' => $replacingId]));

        $data = IrhpPermitMapper::mapFromResult($irhpPermit->getResult());
        $form = $this->getForm('ReplacePermit');
        if (isset($data['restrictedCountries'])) {
            $form->get('restrictedCountries')->setAttribute('value', $data['restrictedCountries']);
        }
        $form->setData($data);

        $view = new ViewModel();
        $view->setTemplate('sections/irhp-permit/pages/replacement');
        $view->setVariable('form', $form);

        return $view;
    }

    /**
     * @return bool
     */
    protected function handleReplacementPost()
    {
        $postParams = $this->params()->fromPost();
        $response = $this->handleCommand(
            ReplaceDTO::create(
                [
                    'id' => $postParams['id'],
                    'replacementIrhpPermit' => $postParams['replacementIrhpPermit']
                ]
            )
        );
        $result = $response->getResult();
        if (!$response->isOk()) {
            foreach ($result['messages'] as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
            return false;
        } else {
            foreach ($result['messages'] as $message) {
                $this->flashMessenger()->addSuccessMessage($message);
            }
            return true;
        }
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function terminatePermitAction()
    {
        if ($this->getRequest()->isPost()) {
            // If post handle suceeds, redirect to index, else re-render in modal to show errors.
            if ($this->handleTerminationPost()) {
                return $this->redirect()->toRouteAjax(
                    'licence/irhp-permits',
                    [
                        'action' => 'index',
                        'licence' => $this->params()->fromRoute('licence'),
                        'permitid' => $this->params()->fromRoute('permitid')
                    ]
                );
            }
        }

        $permitId = $this->params()->fromQuery('irhpPermitId');
        $irhpPermit = $this->handleQuery(ItemDTO::create(['id' => $permitId]));

        $data = $irhpPermit->getResult();
        $form = $this->getForm('TerminatePermit');

        $form->setData($data);

        $view = new ViewModel();
        $view->setTemplate('sections/irhp-permit/pages/replacement');
        $view->setVariable('form', $form);

        return $view;
    }

    /**
     * @return bool
     */
    protected function handleTerminationPost()
    {
        $postParams = $this->params()->fromPost();

        $response = $this->handleCommand(
            TerminateDTO::create(
                [
                    'id' => $postParams['id']
                ]
            )
        );
        $result = $response->getResult();
        if (!$response->isOk()) {
            foreach ($result['messages'] as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
            return false;
        } else {
            $applicationId = $this->params()->fromRoute('permitid');
            $permitsTotal = $this->handleQuery(ValidEcmtPermitsDto::create([
                'page' => 1,
                'limit' => 10,
                'id' => $applicationId,
            ]));

            if ($permitsTotal->getResult()['count'] === 0) {
                $applicationResponse = $this->handleCommand(
                    ExpireDTO::create(
                        [
                            'id' => $applicationId
                        ]
                    )
                );
                if ($applicationResponse->isOk()) {
                    $message = 'The selected application is now expired.';
                    $this->flashMessenger()->addSuccessMessage($message);
                }
            }
            $message = 'The selected permit has been terminated.';
            $this->flashMessenger()->addSuccessMessage($message);
            return true;
        }
    }
}
