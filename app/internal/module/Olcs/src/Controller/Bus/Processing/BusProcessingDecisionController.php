<?php

/**
 * Bus Processing Decision Controller
 */
namespace Olcs\Controller\Bus\Processing;

use Dvsa\Olcs\Transfer\Command\Bus\AdminCancelBusReg as AdminCancelDto;
use Dvsa\Olcs\Transfer\Command\Bus\GrantBusReg as GrantDto;
use Dvsa\Olcs\Transfer\Command\Bus\RefuseBusReg as RefuseDto;
use Dvsa\Olcs\Transfer\Command\Bus\RefuseBusRegByShortNotice as RefuseByShortNoticeDto;
use Dvsa\Olcs\Transfer\Command\Bus\ResetBusReg as ResetDto;
use Dvsa\Olcs\Transfer\Command\Bus\WithdrawBusReg as WithdrawDto;
use Dvsa\Olcs\Transfer\Command\Publication\Bus as PublishDto;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\BusRegUpdateStatus as UpdateStatusMapper;
use Olcs\Form\Model\Form\BusRegUpdateWithdrawn as WithdrawForm;
use Olcs\Form\Model\Form\BusRegUpdateStatus as UpdateStatusForm;
use Olcs\Form\Model\Form\BusRegVariationReason as VariationReasonForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Common\RefData;
use Zend\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use \Zend\Http\Response;

/**
 * Bus Processing Decision Controller
 */
class BusProcessingDecisionController extends AbstractInternalController implements
    BusRegControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus_processing';

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/bus/pages/processing-decision';
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'busRegId'];

    protected $redirectConfig = [
        'cancel' => [
            'action' => 'details'
        ],
        'grant' => [
            'action' => 'details'
        ],
        'refuse' => [
            'action' => 'details'
        ],
        'refuse-by-short-notice' => [
            'action' => 'details'
        ],
        'withdraw' => [
            'action' => 'details'
        ],
        'reset' => [
            'action' => 'details'
        ],
        'republish' => [
            'action' => 'details'
        ],
        'index' => [
            'action' => 'details'
        ]
    ];

    /**
     * get method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/bus/partials/left');

        return $view;
    }

    /**
     * index action
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->redirectTo([]);
    }

    /**
     * adding an entity
     *
     * @return Response
     */
    public function addAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Edit an entity
     *
     * @return Response
     */
    public function editAction()
    {
        return $this->notFoundAction();
    }

    /**
     * delete an entity
     *
     * @return Response
     */
    public function deleteAction()
    {
        return $this->notFoundAction();
    }
    /**
     * cancel action
     *
     * @return Response
     **/
    public function cancelAction()
    {
        return $this->add(
            UpdateStatusForm::class,
            new AddFormDefaultData($this->getDefaultData()),
            AdminCancelDto::class,
            UpdateStatusMapper::class,
            'pages/crud-form',
            'Updated record',
            'Update status'
        );
    }

    /**
     * grantAction
     *
     * @return Response
     */
    public function grantAction()
    {
        $query = ItemDto::create($this->getDefaultData());
        $response = $this->handleQuery($query);

        if ($response->isOk()) {
            $busReg = $response->getResult();

            if (empty($busReg) || !$busReg['isGrantable']) {
                // not grantable
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addErrorMessage('The record is not grantable');
                return $this->redirectTo([]);
            }

            if ($busReg['status']['id'] === RefData::BUSREG_STATUS_VARIATION) {
                // variation reason needed
                return $this->processGrantVariation();
            } else {
                //grant
                return $this->processCommand(new GenericItem($this->itemParams), GrantDto::class);
            }
        } else {
            // can't get the record
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $this->redirectTo([]);
    }

    /**
     * refuse action
     *
     * @return ViewModel
     */
    public function refuseAction()
    {
        return $this->add(
            UpdateStatusForm::class,
            new AddFormDefaultData($this->getDefaultData()),
            RefuseDto::class,
            UpdateStatusMapper::class,
            'pages/crud-form',
            'Updated record',
            'Update status'
        );
    }

    /**
     * refuseByShortNoticeAction
     *
     * @return ViewModel
     */
    public function refuseByShortNoticeAction()
    {
        return $this->add(
            UpdateStatusForm::class,
            new AddFormDefaultData($this->getDefaultData()),
            RefuseByShortNoticeDto::class,
            UpdateStatusMapper::class,
            'pages/crud-form',
            'Updated record',
            'Update status'
        );
    }

    /**
     * republish action
     *
     * @return Response
     */
    public function republishAction()
    {
        return $this->processCommand(new GenericItem($this->itemParams), PublishDto::class);
    }

    /**
     * reset action
     *
     * @return Response
     */
    public function resetAction()
    {
        return $this->processCommand(new GenericItem($this->itemParams), ResetDto::class);
    }

    /**
     * withdraw action
     *
     * @return ViewModel
     */
    public function withdrawAction()
    {
        return $this->add(
            WithdrawForm::class,
            new AddFormDefaultData($this->getDefaultData()),
            WithdrawDto::class,
            UpdateStatusMapper::class,
            'pages/crud-form',
            'Updated record',
            'Update status'
        );
    }

    /**
     * getDefault Data
     *
     * @return array
     */
    private function getDefaultData()
    {
        return ['id' => $this->params()->fromRoute('busRegId')];
    }

    /**
     * process grant variation
     *
     * @return ViewModel
     */
    protected function processGrantVariation()
    {
        return $this->add(
            VariationReasonForm::class,
            new AddFormDefaultData($this->getDefaultData()),
            GrantDto::class,
            UpdateStatusMapper::class,
            'pages/crud-form',
            'Updated record',
            'Grant variation'
        );
    }
}
