<?php

namespace Olcs\Controller\Licence;

use Common\Controller\Traits\GenericRenderView;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Surrender\Approve as ApproveSurrender;
use Dvsa\Olcs\Transfer\Command\Surrender\Withdraw as WithdrawSurrender;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenBusReg;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenCases;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Traits\LicenceControllerTrait;
use Olcs\Form\Model\Form\Licence\Surrender\Confirmation;
use Olcs\Form\Model\Form\Licence\Surrender\Surrender;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class SurrenderController extends AbstractInternalController
{
    use GenericRenderView, LicenceControllerTrait;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_surrender';

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
     * @param MvcEvent $e
     *
     * @return array|mixed
     */

    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        $this->licence = $this->getLicence($this->licenceId);
        $this->licenceType = $this->licence['goodsOrPsv']['id'];
        return parent::onDispatch($e);
    }

    /**
     * index Action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->setupData();
        return $this->getView();
    }

    public function surrenderAction()
    {


        $this->setupData();
        $this->form->setData($this->getRequest()->getPost());

        $canSurrender = $this->form->isValid();
        if ($this->counts['openCases'] > 0 && $this->counts['busRegistrations'] > 0) {
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
        /** @var TranslationHelperService $translator
         */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

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
    }

    public function alterTable($table, $data)
    {
        $tableName = $table->getAttributes()['name'];
        $this->counts[$tableName] = $data['count'];
        return $table;
    }

    private function setupData()
    {
        $this->setupCasesTable();
        $this->setupBusRegTable();

        $this->form = $this->getForm(Surrender::class);
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
            'Surrender details'
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
            new GenericList([
                'id' => 'licence',
            ], 'licId'),
            'busRegistrations',
            'licence-surrender-busreg',
            $this->tableViewTemplate
        );
    }

    private function surrenderLicence(int $licenceId): bool
    {
        $surrenderDate = new \DateTime();
        $command = ApproveSurrender::create([
            'id' => $licenceId,
            'surrenderDate' => $surrenderDate->format('Y-m-d')
        ]);
        $response = $this->handleCommand($command);
        return $response->isOk();
    }

    private function withdrawSurrender(int $licenceId): bool
    {
        $command = WithdrawSurrender::create([
            'id' => $licenceId
        ]);

        $response = $this->handleCommand($command);
        return $response->isOk();
    }
}
