<?php

namespace Olcs\Controller\Licence;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Traits\GenericRenderView;
use Common\Exception\BadRequestException;
use Common\FeatureToggle;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Surrender\Approve as ApproveSurrender;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as UpdateSurrender;
use Dvsa\Olcs\Transfer\Command\Surrender\Withdraw as WithdrawSurrender;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenBusReg;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenCases;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Traits\LicenceControllerTrait;
use Olcs\Form\Model\Form\Licence\Surrender\Confirmation;
use Olcs\Form\Model\Form\Licence\Surrender\Surrender;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;

class SurrenderController extends AbstractInternalController implements
    ToggleAwareInterface,
    LeftViewProvider,
    LicenceControllerInterface
{
    use GenericRenderView;
    use LicenceControllerTrait;

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::INTERNAL_SURRENDER
        ],
    ];

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_surrender';

    protected $inlineScripts = [
        'indexAction' => ['forms/surrender'],
        'surrenderAction' => ['forms/surrender']
    ];

    /**
     * @var array
     */
    protected $counts;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var int LicenceId
     */
    protected $licenceId;

    /**
     * @var string licence type
     */
    protected $licenceType;

    /**
     * @var array licence
     */
    protected $licence;

    /**
     * @var array
     */
    private $surrender;


    /**
     * @param MvcEvent $e
     *
     * @return array|mixed
     */

    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        $this->surrender = $this->getSurrender($this->licenceId);
        $this->licence = $this->surrender['licence'];
        $this->licenceType = $this->licence['goodsOrPsv']['id'];
        return parent::onDispatch($e);
    }

    /**
     * index Action
     *
     * @return \Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $this->setupData();
        $view = $this->getView();
        $this->placeholder()->setPlaceholder(
            'openItems',
            $this->counts['openCases'] + $this->counts['busRegistrations']
        );
        return $view;
    }

    public function surrenderAction()
    {
        $this->setupData();
        $this->form->setData($this->getRequest()->getPost());

        $canSurrender = $this->form->isValid();
        if ($this->counts['openCases'] > 0 || $this->counts['busRegistrations'] > 0) {
            $this->flashMessenger()->addErrorMessage('licence.surrender.internal.surrender.error.open_case_active_bus');
            $canSurrender = false;
        }

        if (!$canSurrender) {
            return $this->getView();
        }

        if (!$this->surrenderLicence($this->licenceId)) {
            $this->flashMessenger()->addErrorMessage('licence.surrender.internal.surrender.error.generic');
            return $this->getView();
        }

        $this->flashMessenger()->addSuccessMessage('licence-status.surrender.message.save.success');
        return $this->redirect()->toRoute('licence', [], [], true);
    }

    public function withdrawAction()
    {
        /**
 * @var TranslationHelperService $translator
         */
        $translator = $this->translationHelperService;

        $form = $this->getForm(Confirmation::class);
        $message = $translator->translateReplace(
            'licence.surrender.internal.withdraw.confirm.message',
            [$this->licence['licNo']]
        );
        $form->get('messages')->get('message')->setValue($message);

        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Confirm Withdraw');
    }

    public function confirmWithdrawAction()
    {
        if ($this->withdrawSurrender($this->licenceId)) {
            $this->flashMessenger()->addSuccessMessage('licence-status.surrender.message.withdrawn');
            return $this->redirect()->toRouteAjax('licence', [], [], true);
        }
        $this->flashMessenger()->addErrorMessage("licence.surrender.internal.withdraw.error");
        return $this->redirect()->refresh();
    }

    public function surrenderChecksAction()
    {
        $checkboxData = $this->getRequest()->getPost();
        $updateCmdData = [];
        $approvedNames = ['signatureChecked', 'ecmsChecked'];

        foreach ($checkboxData as $checkboxName => $checkboxValue) {
            if (!in_array($checkboxName, $approvedNames)) {
                continue;
            }
            if ($checkboxValue === "1" || $checkboxValue === "0") {
                $updateCmdData[$checkboxName] = $checkboxValue;
            }
        }

        if (empty($updateCmdData)) {
            throw new BadRequestException('No data supplied to command');
        }

        $this->flashMessenger()->clearCurrentMessagesFromContainer();
        if ($this->updateSurrender($updateCmdData)) {
            $this->flashMessenger()->addSuccessMessage('successful-changes');
        } else {
            $this->flashMessenger()->addErrorMessage('unsuccessful-changes');
        }

        return $this->redirect()->toRouteAjax('licence/surrender-details/GET', [], [], true);
    }

    public function alterLayout()
    {
        foreach ($this->counts as $key => $value) {
            if ($value === 0) {
                $this->placeholder()->setPlaceholder($key, '');
            } else {
                $this->form->get('checks')->remove($key);
            }
        }
        if ($this->licenceType === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->form->get('checks')->remove('busRegistrations');
        }

        if ($this->surrender['signatureType']['id'] === RefData::SIGNATURE_TYPE_PHYSICAL_SIGNATURE) {
            $this->form->get('checks')->get('digitalSignature')->setLabel('Physical signature has been checked');
        }
    }

    #[\Override]
    public function alterTable($table, $data)
    {
        $tableName = $table->getAttributes()['name'];
        $this->counts[$tableName] = $data['count'];
        return $table;
    }

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/licence/partials/surrender/left');
        return $view;
    }

    private function setupData()
    {
        $this->setupCasesTable();
        $this->setupBusRegTable();

        $this->form = $this->getForm(Surrender::class);
        $this->maybeCheckCheckboxes();
    }

    private function maybeCheckCheckboxes(): void
    {
        $signatureChecked = $this->surrender['signatureChecked'] ?? false;
        $ecmsChecked = $this->surrender['ecmsChecked'] ?? false;
        if ($signatureChecked === true) {
            $this->form->get('checks')->get('digitalSignature')->setAttribute('checked', 'checked');
        }
        if ($ecmsChecked === true) {
            $this->form->get('checks')->get('ecms')->setAttribute('checked', 'checked');
        }
    }

    private function getView()
    {
        $this->placeholder()->setPlaceholder('form', $this->form);

        $this->alterLayout();

        return $this->details(
            ByLicence::class,
            new GenericItem(['id' => 'licence']),
            'details',
            'sections/licence/pages/surrender',
            'Summary: Application to surrender an operator licence'
        );
    }

    /**
     * Setup Oppositions table
     *
     * @return void
     */
    private function setupCasesTable()
    {
        $this->index(
            OpenCases::class,
            new GenericList(['id' => 'licence'], 'id'),
            'openCases',
            'open-cases',
            $this->tableViewTemplate
        );
    }

    /**
     * Setup Environment Complaints table
     *
     * @return void
     */
    private function setupBusRegTable()
    {
        if ($this->licenceType === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->counts['busRegistrations'] = 0;
            return;
        }
        $this->index(
            OpenBusReg::class,
            new GenericList(
                [
                'id' => 'licence',
                ],
                'licId'
            ),
            'busRegistrations',
            'licence-surrender-busreg',
            $this->tableViewTemplate
        );
    }

    private function surrenderLicence(int $licenceId): bool
    {
        $surrenderDate = new \DateTime();
        $command = ApproveSurrender::create(
            [
            'id' => $licenceId,
            'surrenderDate' => $surrenderDate->format('Y-m-d')
            ]
        );
        $response = $this->handleCommand($command);
        return $response->isOk();
    }

    private function withdrawSurrender(int $licenceId): bool
    {
        $command = WithdrawSurrender::create(
            [
            'id' => $licenceId
            ]
        );

        $response = $this->handleCommand($command);
        return $response->isOk();
    }

    private function getSurrender(int $licenceId)
    {
        $response = $this->handleQuery(
            ByLicence::create(
                [
                'id' => $licenceId
                ]
            )
        );

        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to get Surrender data');
        }

        return $response->getResult();
    }

    private function updateSurrender(array $updateCmdData): bool
    {
        $requiredCmdData = [
            'id' => $this->licenceId,
            'version' => $this->surrender['version']
        ];

        $cmdData = array_merge($requiredCmdData, $updateCmdData);
        $response = $this->handleCommand(UpdateSurrender::create($cmdData));

        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to update Surrender data');
        }
        return $response->isOk();
    }
}
