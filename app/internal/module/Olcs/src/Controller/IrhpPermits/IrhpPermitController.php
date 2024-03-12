<?php

namespace Olcs\Controller\IrhpPermits;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCandidatePermitSelection;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Replace as ReplaceDTO;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Terminate as TerminateDTO;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication as UnpaidPermitsDto;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged as UnpaidPermitsDtoUnpaged;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByIrhpId as IrhpListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\IrhpPermit as IrhpPermitMapper;

class IrhpPermitController extends AbstractInternalController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider
{
    protected $itemParams = ['id' => 'irhppermitid'];
    protected $deleteParams = ['id' => 'irhppermitid'];

    protected $tableName = 'irhp-permits';
    protected $defaultTableSortField = 'permitNumber';
    protected $defaultTableOrderField = 'DESC';

    protected $listVars = ['irhpApplication' => 'irhpAppId'];
    protected $listDto = IrhpListDTO::class;
    protected $itemDto = ItemDto::class;

    protected $hasMultiDelete = false;

    // After Adding and Editing we want users taken back to index dashboard
    protected $redirectConfig = [];

    protected $navigationId = 'irhp_permits-permits';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    /**
     * Get left view
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();

        $view->setTemplate('sections/irhp-application/partials/left');

        return $view;
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postParams = $this->params()->fromPost();
            if (isset($postParams['action'])) {
                switch ($postParams['action']) {
                    case 'Terminate':
                        $action = 'terminatePermit';
                        break;
                    case 'Request Replacement':
                        $action = 'requestReplacement';
                        break;
                    case 'Save':
                        return $this->handleCandidateChoices($postParams);
                }

                return $this->redirect()->toRoute(
                    'licence/irhp-application/irhp-permits',
                    [
                        'action' => $action,
                        'irhpPermitId' => $postParams['id']
                    ],
                    ['query' => ['irhpPermitId' => $postParams['id']]],
                    true
                );
            }
        }

        $appQuery = $this->handleQuery(ById::create(['id' => $this->params()->fromRoute('irhpAppId')]));
        $irhpApplication = $appQuery->getResult();

        if ($irhpApplication['canSelectCandidatePermits']) {
            $this->tableName = 'irhp-permits-ecmt-candidate-partial-select';
            $this->listDto = UnpaidPermitsDtoUnpaged::class;
            $this->tableViewTemplate = 'pages/irhp-permit/choose-candidate-permits';
            $this->defaultTableSortField = 'id';
        } elseif ($irhpApplication['canViewCandidatePermits']) {
            $this->tableName = 'irhp-permits-ecmt-candidate-preview';
            $this->listDto = UnpaidPermitsDto::class;
            $this->defaultTableSortField = 'id';
        }

        return parent::indexAction();
    }

    /**
     * Handles POST from candidate deselection index page variant
     *
     * @param array $postParams
     *
     * @return \Laminas\Http\Response
     */
    private function handleCandidateChoices(array $postParams)
    {
        $updateCandidateChoicesCmd = $this->handleCommand(
            UpdateCandidatePermitSelection::create(
                [
                    'id' => $this->params()->fromRoute('irhpAppId'),
                    'selectedCandidatePermitIds' => $postParams['id']
                ]
            )
        );

        if (!$updateCandidateChoicesCmd->isOk()) {
            $this->flashMessenger()->addErrorMessage('An error occurred saving permit selections.');
            foreach ($updateCandidateChoicesCmd['messages'] as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
        } else {
            $this->flashMessenger()->addSuccessMessage('Permit selections saved successfully.');
        }

        return $this->redirect()->toRouteAjax(
            'licence/irhp-application/irhp-permits',
            [
                'action' => 'index',
                'irhpAppId' => $this->params()->fromRoute('irhpAppId')
            ],
            [],
            true
        );
    }

    /**
     * Alter table
     *
     * @param \Common\Service\Table\TableBuilder $table table
     * @param array                              $data  data
     *
     * @return                                        \Common\Service\Table\TableBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function alterTable($table, $data)
    {
        // Get Permit Type from route, switch columns if required
        $permitTypeId = intval($this->params()->fromRoute('permitTypeId'));

        switch ($permitTypeId) {
            case RefData::ECMT_PERMIT_TYPE_ID:
                $table->removeColumn('type');
                break;
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                $table->removeColumn('type');
                $table->removeColumn('country');
                break;
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                $table->removeColumn('emissionsCategory');
                $table->removeColumn('constrainedCountries');
                break;
            default:
                $table->removeColumn('type');
                $table->removeColumn('emissionsCategory');
                $table->removeColumn('constrainedCountries');
                $table->removeColumn('country');
                break;
        }

        return $table;
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function requestReplacementAction()
    {
        if ($this->getRequest()->isPost()) {
            // If post handle suceeds, redirect to index, else re-render in modal to show errors.
            if ($this->handleReplacementPost()) {
                return $this->redirect()->toRouteAjax(
                    'licence/irhp-application/irhp-permits',
                    [
                        'action' => 'index',
                        'licence' => $this->params()->fromRoute('licence'),
                        'irhpAppId' => $this->params()->fromRoute('irhpAppId'),
                        'permitTypeId' => $this->params()->fromRoute('permitTypeId')
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
            $form->remove('country');
        } elseif (isset($data['irhpPermitRange']['irhpPermitStock']['country'])) {
            $form->get('country')->setAttribute('value', $data['country']);
            $form->remove('restrictedCountries');
        }

        $form->setData($data);

        $view = new ViewModel();
        $view->setTemplate('sections/irhp-permit/pages/application-permits-modal');
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
     * @return \Laminas\Http\Response|ViewModel
     */
    public function terminatePermitAction()
    {
        if ($this->getRequest()->isPost()) {
            // If post handle suceeds, redirect to index, else re-render in modal to show errors.
            if ($this->handleTerminationPost()) {
                return $this->redirect()->toRouteAjax(
                    'licence/irhp-application/irhp-permits',
                    [
                        'action' => 'index',
                        'licence' => $this->params()->fromRoute('licence'),
                        'irhpAppId' => $this->params()->fromRoute('irhpAppId'),
                        'permitTypeId' => $this->params()->fromRoute('permitTypeId'),
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
        $view->setTemplate('sections/irhp-permit/pages/application-permits-modal');
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
        }

        $this->flashMessenger()->addSuccessMessage($result['messages'][0]);
        return true;
    }
}
