<?php

namespace Olcs\Controller\Licence;

use Common\Controller\Interfaces\MethodToggleAwareInterface;
use Common\Controller\Lva\Traits\MethodToggleTrait;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Licence\CurtailLicence;
use Dvsa\Olcs\Transfer\Command\Licence\ResetToValid;
use Dvsa\Olcs\Transfer\Command\Licence\RevokeLicence;
use Dvsa\Olcs\Transfer\Command\Licence\SurrenderLicence;
use Dvsa\Olcs\Transfer\Command\Licence\SuspendLicence;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\CreateLicenceStatusRule;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\DeleteLicenceStatusRule;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\UpdateLicenceStatusRule;
use Dvsa\Olcs\Transfer\Command\Surrender\Withdraw;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceDecisions;
use Dvsa\Olcs\Transfer\Query\LicenceStatusRule\LicenceStatusRule;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Traits\LicenceControllerTrait;

class LicenceDecisionsController extends AbstractController implements
    LicenceControllerInterface,
    MethodToggleAwareInterface
{
    use LicenceControllerTrait;
    use MethodToggleTrait;

    protected $methodToggles = [
        'withdrawSurrender' => [FeatureToggle::INTERNAL_SURRENDER],
    ];

    protected $undoCommand;

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected $navigation;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelper,
        $navigation
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->navigation = $navigation;
    }

    /**
     * Display messages and enable to user to carry on to a decision if applicable.
     *
     * @return string|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function activeLicenceCheckAction()
    {
        $decision = $this->fromRoute('decision', null);
        $licence = $this->fromRoute('licence', null);

        $formHelper = $this->formHelper;

        $form = $formHelper->createFormWithRequest('LicenceStatusDecisionMessages', $this->getRequest());

        $query = LicenceDecisions::create(
            [
                'id' => $licence
            ]
        );

        $response = $this->handleQuery($query);
        $result = $response->getResult();

        $pageTitle = ucfirst($decision) . " licence";
        if (
            !isset($result['suitableForDecisions']) || $this->getRequest()->isPost()
            || $result['suitableForDecisions'] === true
        ) {
            return $this->redirectToDecision($decision, $licence);
        }

        if (isset($result['suitableForDecisions']) && is_array($result['suitableForDecisions'])) {
            $messages = [];
            foreach ($result['suitableForDecisions'] as $key => $value) {
                if (!$value) {
                    continue;
                }

                switch ($key) {
                    case 'activeComLics':
                        $messages[$key] = 'There are active, pending or suspended community licences';
                        break;
                    case 'activeBusRoutes':
                        $messages[$key] = 'There are active bus routes on this licence';
                        break;
                    case 'activeVariations':
                        $messages[$key] = 'There are applications still under consideration';
                        break;
                    case 'activePermits':
                        $messages[$key] = 'There are active permits on this licence';
                        break;
                    case 'ongoingPermitApplications':
                        $messages[$key] = 'There are ongoing permit applications on this licence';
                        break;
                    case 'validCorPermitApplications':
                        $messages[$key] = 'There are active certificates on this licence';
                        break;
                }
            }
            $form->get('messages')->get('message')->setValue(implode('<br>', $messages));
        }

        $view = $this->getViewWithLicence(
            [
                'form' => $form
            ]
        );

        $view->setTemplate('pages/form');

        return $this->renderView($view, $pageTitle);
    }

    /**
     * Curtail a licence.
     *
     * @return string|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function curtailAction()
    {
        $licenceId = $this->fromRoute('licence');
        // @todo it seems that the following part never going to work and possibly can be removed
        $licenceStatus = $this->fromRoute('status', null);

        if (!is_null($licenceStatus)) {
            if ($this->isButtonPressed('remove')) {
                return $this->removeLicenceStatusRule(
                    $licenceId,
                    $licenceStatus,
                    'licence-status.curtailment.message.remove.success'
                );
            }

            $licenceStatus = $this->getStatusForLicenceById($licenceStatus);

            // get decisions into array of ids for array map
            $licenceStatus['legislationDecisions'] = array_column($licenceStatus['licence']['decisions'], 'id');
        }
        // -----

        if ($this->isButtonPressed('affectImmediate')) {
            $postData = $this->getRequest()->getPost();
            return $this->affectImmediate(
                array_merge(
                    ['licenceId' => $licenceId],
                    ['decisions' => $postData['licence-decision-legislation']['decisions']]
                ),
                CurtailLicence::class,
                'licence-status.curtailment.message.save.success'
            );
        }

        $form = $this->getDecisionForm(
            'LicenceStatusDecisionCurtail',
            $licenceStatus,
            [
                'curtailFrom' => 'startDate',
                'curtailTo' => 'endDate'
            ]
        );

        $form->get('licence-decision-legislation')->get('decisions')->setValue($licenceStatus['legislationDecisions']);

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $response = $this->saveDecisionForLicence(
                    $licenceId,
                    [
                        'status' => RefData::LICENCE_STATUS_RULE_CURTAILED,
                        'startDate' => $formData['licence-decision']['curtailFrom'],
                        'endDate' => $formData['licence-decision']['curtailTo'],
                        'decisions' => $formData['licence-decision-legislation']['decisions']
                    ],
                    $licenceStatus
                );

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('licence-status.curtailment.message.save.success');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        return $this->renderDecisionView($form, 'Curtail licence');
    }

    /**
     * Revoke a licence.
     *
     * @return string|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function revokeAction()
    {
        $licenceId = $this->fromRoute('licence');
        // @todo it seems that the following part never going to work and possibly can be removed
        $licenceStatus = $this->fromRoute('status', null);
        if (!is_null($licenceStatus)) {
            if ($this->isButtonPressed('remove')) {
                return $this->removeLicenceStatusRule(
                    $licenceId,
                    $licenceStatus,
                    'licence-status.revocation.message.remove.success'
                );
            }

            $licenceStatus = $this->getStatusForLicenceById($licenceStatus);

            // get decisions into array of ids for array map
            $licenceStatus['legislationDecisions'] = array_column($licenceStatus['licence']['decisions'], 'id');
        }
        // -----

        if ($this->isButtonPressed('affectImmediate')) {
            $postData = $this->getRequest()->getPost();

            return $this->affectImmediate(
                array_merge(
                    ['licenceId' => $licenceId],
                    ['decisions' => $postData['licence-decision-legislation']['decisions']]
                ),
                RevokeLicence::class,
                'licence-status.revocation.message.save.success'
            );
        }

        $form = $this->getDecisionForm(
            'LicenceStatusDecisionRevoke',
            $licenceStatus,
            [
                'revokeFrom' => 'startDate'
            ]
        );
        $form->get('licence-decision-legislation')->get('decisions')->setValue($licenceStatus['legislationDecisions']);

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $response = $this->saveDecisionForLicence(
                    $licenceId,
                    [
                        'status' => RefData::LICENCE_STATUS_RULE_REVOKED,
                        'startDate' => $formData['licence-decision']['revokeFrom'],
                        'decisions' => $formData['licence-decision-legislation']['decisions']
                    ],
                    $licenceStatus
                );

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('licence-status.revocation.message.save.success');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        return $this->renderDecisionView($form, 'Revoke licence');
    }

    /**
     * Suspend a licence.
     *
     * @return string|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function suspendAction()
    {
        $licenceId = $this->fromRoute('licence');

        // @todo it seems that the following part never going to work and possibly can be removed
        $licenceStatus = $this->fromRoute('status', null);
        if (!is_null($licenceStatus)) {
            if ($this->isButtonPressed('remove')) {
                return $this->removeLicenceStatusRule(
                    $licenceId,
                    $licenceStatus,
                    'licence-status.suspension.message.remove.success'
                );
            }

            $licenceStatus = $this->getStatusForLicenceById($licenceStatus);

            // get decisions into array of ids for array map
            $licenceStatus['legislationDecisions'] = array_column($licenceStatus['licence']['decisions'], 'id');
        }
        // -------

        if ($this->isButtonPressed('affectImmediate')) {
            $postData = $this->getRequest()->getPost();
            return $this->affectImmediate(
                array_merge(
                    ['licenceId' => $licenceId],
                    ['decisions' => $postData['licence-decision-legislation']['decisions']]
                ),
                SuspendLicence::class,
                'licence-status.suspension.message.save.success'
            );
        }

        $form = $this->getDecisionForm(
            'LicenceStatusDecisionSuspend',
            $licenceStatus,
            [
                'suspendFrom' => 'startDate',
                'suspendTo' => 'endDate'
            ]
        );
        $form->get('licence-decision-legislation')->get('decisions')->setValue($licenceStatus['legislationDecisions']);

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();
                $response = $this->saveDecisionForLicence(
                    $licenceId,
                    [
                        'status' => RefData::LICENCE_STATUS_RULE_SUSPENDED,
                        'startDate' => $formData['licence-decision']['suspendFrom'],
                        'endDate' => $formData['licence-decision']['suspendTo'],
                        'decisions' => $formData['licence-decision-legislation']['decisions']
                    ],
                    $licenceStatus
                );

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('licence-status.suspension.message.save.success');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        return $this->renderDecisionView($form, 'Suspend licence');
    }

    /**
     * Reset the licence back to a valid state.
     *
     * @return string|\Laminas\View\Model\ViewModel
     */
    public function resetToValidAction()
    {
        $pageTitle = $this->params('title') ?: 'licence-status.reset.title';

        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('GenericConfirmation');
        $form->get('messages')
            ->get('message')
            ->setValue('licence-status.reset.message');
        $form->get('form-actions')
            ->get('submit')
            ->setLabel('licence-status.reset.title');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $response = $this->handleCommand(
                    ResetToValid::create(
                        [
                            'id' => $licenceId,
                            'decisions' => []
                        ]
                    )
                );

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('licence-status.reset.message.save.success');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        $view = $this->getView(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, $pageTitle);
    }

    public function undoSurrenderAction()
    {
        $pageTitle = $this->params('title') ?: 'Undo surrender';

        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('GenericConfirmation');
        $form->get('messages')
            ->get('message')
            ->setValue('Are you sure you want to undo the surrender of this licence?');
        $form->get('form-actions')
            ->get('submit')
            ->setLabel('Undo surrender');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $data = ['id' => $licenceId];
                $this->undoCommand = ResetToValid::create(
                    [
                        'id' => $licenceId,
                        'decisions' => []
                    ]
                );
                $this->togglableMethod(
                    $this,
                    'withdrawSurrender',
                    $data
                );

                $response = $this->handleCommand($this->undoCommand);
                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('The licence surrender has been undone');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        $view = $this->getView(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, $pageTitle);
    }

    /**
     * Surrender a licence.
     *
     * @return string|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function surrenderAction()
    {
        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('LicenceStatusDecisionSurrender');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $command = SurrenderLicence::create(
                    [
                        'id' => $licenceId,
                        'surrenderDate' => $formData['licence-decision']['surrenderDate'],
                        'terminated' => false,
                        'decisions' => $formData['licence-decision-legislation']['decisions']
                    ]
                );

                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('licence-status.surrender.message.save.success');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        $this->formHelper->setDefaultDate(
            $form->get('licence-decision')->get('surrenderDate')
        );
        $form->get('form-actions')->get('confirm')->setLabel('licence-status.surrender.surrender-button');

        return $this->renderDecisionView($form, 'Surrender licence');
    }

    /**
     * Terminate a licence.
     *
     * @return string|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function terminateAction()
    {
        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('LicenceStatusDecisionTerminate');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $command = SurrenderLicence::create(
                    [
                        'id' => $licenceId,
                        'surrenderDate' => $formData['licence-decision']['terminateDate'],
                        'terminated' => true
                    ]
                );

                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('licence-status.terminate.message.save.success');
                    return $this->redirectToRouteAjax('licence', ['licence' => $licenceId]);
                }
            }
        }

        $this->formHelper->setDefaultDate(
            $form->get('licence-decision')->get('terminateDate')
        );
        $form->get('form-actions')->get('confirm')->setLabel('licence-status.terminate.terminate-button');

        return $this->renderDecisionView($form, 'Terminate licence');
    }

    /**
     * If a xNow e.g. curtailNow method has been pressed then redirect.
     *
     * @param array       $data    The licence id.
     * @param null|string $command The command to use.
     * @param null|string $message The message to display
     *
     * @return \Laminas\Http\Response A redirection response.
     */
    private function affectImmediate($data = [], $command = null, $message = null)
    {
        $command = $command::create(
            [
                'id' => $data['licenceId'],
                'decisions' => $data['decisions'] ?? []
            ]
        );

        $response = $this->handleCommand($command);

        if ($response->isOk()) {
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirectToRouteAjax(
                'licence',
                [
                    'licence' => $data['licenceId']
                ]
            );
        }
    }

    /**
     * Get the decision form.
     *
     * @param null|string $name   The form name to try and get.
     * @param null|array  $status Licence status rule.
     * @param null|array  $keys   Keys to map.
     *
     * @return mixed The form.
     */
    private function getDecisionForm($name = null, $status = null, array $keys = [])
    {
        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest($name, $this->getRequest());

        if (!is_null($status)) {
            return $form->setData(
                $this->formatDataForFormUpdate(
                    array_map(
                        fn($key) => $status[$key],
                        $keys
                    )
                )
            );
        }

        $form->get('form-actions')->remove('remove');

        return $form;
    }

    /**
     * Save/update a decision against a licence.
     *
     * @param null|int   $licenceId  The licence id.
     * @param array      $data       The data to save.
     * @param array|null $statusRule The licence status record.
     *
     * @return LicenceControllerInterface
     */
    private function saveDecisionForLicence($licenceId = null, array $data = [], $statusRule = null)
    {
        $data['licence'] = $licenceId;

        if (!is_null($statusRule)) {
            $command = new UpdateLicenceStatusRule();
            $command->exchangeArray(
                array_merge(
                    $data,
                    [
                        'id' => $statusRule['id'],
                        'version' => $statusRule['version']
                    ]
                )
            );

            return $this->handleCommand($command);
        }

        $command = CreateLicenceStatusRule::create($data);

        return $this->handleCommand($command);
    }

    /**
     * Render the view with the form.
     *
     * @param null|\Common\Form\Form $form      The form to render.
     * @param bool                   $pageTitle Whether tp load the script files.
     *
     * @return string|\Laminas\View\Model\ViewModel
     */
    private function renderDecisionView($form = null, $pageTitle = null)
    {
        $view = $this->getViewWithLicence(['form' => $form]);

        $this->scriptFactory->loadFiles(['forms/licence-decision']);

        $view->setTemplate('pages/form');

        return $this->renderView($view, $pageTitle);
    }

    /**
     * Redirect the request to a specific decision.
     *
     * @param null|string $decision The decision.
     * @param null|int    $licence  The licence id.
     *
     * @return \Laminas\Http\Response The redirection
     */
    private function redirectToDecision($decision = null, $licence = null)
    {
        return $this->redirectToRoute(
            'licence/' . $decision . '-licence',
            [
                'licence' => $licence
            ]
        );
    }

    /**
     * Get a licence status.
     *
     * @param int $id The licence status id.
     *
     * @return array $validFormData The licence status data for the form.
     */
    private function getStatusForLicenceById($id)
    {
        $query = LicenceStatusRule::create(
            [
                'id' => $id
            ]
        );

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        return $response->getResult();
    }

    /**
     * Remove the licence status rule record.
     *
     * @param string $licence         The licence id.
     * @param int    $licenceStatusId The licence status id.
     * @param string $message         The message to display.
     *
     * @return mixed
     */
    private function removeLicenceStatusRule($licence, $licenceStatusId, $message)
    {
        $command = DeleteLicenceStatusRule::create(
            [
                'id' => $licenceStatusId
            ]
        );

        $response = $this->handleCommand($command);
        if ($response->isOk()) {
            $this->flashMessenger()->addSuccessMessage($message);

            return $this->redirectToRouteAjax(
                'licence',
                [
                    'licence' => $licence
                ]
            );
        }

        return false;
    }

    /**
     * Return an array that can be set on the form.
     *
     * @param array $licenceDecision The licence decision data.
     * @param array $decisions       decisions
     *
     * @return array The formatted data
     */
    private function formatDataForFormUpdate(
        array $licenceDecision = [],
        array $decisions = []
    ) {
        return [
            'licence-decision-affect-immediate' => [
                'immediateAffect' => 'N',
            ],
            'licence-decision' => $licenceDecision,
            'licence-decision-legislation' => $decisions
        ];
    }

    /**
     * @param array $data
     *
     * @throws NotFoundException
     */
    protected function withdrawSurrender(array $data)
    {
        try {
            $this->handleQuery(ByLicence::create($data));
        } catch (NotFoundException $exception) {
            return;
        }
        $this->undoCommand = Withdraw::create($data);
    }
}
