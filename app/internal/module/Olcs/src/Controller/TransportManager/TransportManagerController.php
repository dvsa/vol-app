<?php

namespace Olcs\Controller\TransportManager;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerController extends AbstractController implements TransportManagerControllerInterface
{
    /**
     * Holds the navigation ID, required when an entire controller is represented by a single navigation id.
     */
    protected $navigationId;

    /**
     * Memoize TM details to prevent multiple backend calls with same id
     * @var array
     */
    protected $tmDetailsCache = [];

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('transport-manager/details', [], [], true);
    }

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexProcessingJumpAction()
    {
        return $this->redirect()->toRoute(
            'transport-manager/processing/notes',
            ['action' => null, 'id' => null, 'transportManager' => $this->params()->fromRoute('transportManager')],
            ['code' => '303'],
            false
        );
    }

    /**
     * Get view with TM
     *
     * @todo this can probably be removed now
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithTm($variables = [])
    {
        return $this->getView($variables);
    }

    public function getTmDetails($tmId, $bypassCache = false)
    {
        if ($bypassCache || !isset($this->tmDetailsCache[$tmId])) {
             $this->tmDetailsCache[$tmId] = $this->getServiceLocator()
                ->get('Entity\TransportManager')
                ->getTmDetails($tmId);
        }
        return $this->tmDetailsCache[$tmId];
    }

    /**
     * Merge a transport manager
     */
    public function mergeAction()
    {
        $transportManagerId = (int) $this->params()->fromRoute('transportManager');

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $tmData = $this->getTransportManager($transportManagerId);
            if (!$tmData) {
                return $this->notFoundAction();
            }
            $data['fromTmName'] = $tmData['id'] .' '.
                $tmData['homeCd']['person']['forename'] .' '.
                $tmData['homeCd']['person']['familyName'];
            if (isset($tmData['users'][0])) {
                $data['fromTmName'] .= ' (associated user: '. $tmData['users'][0]['loginId'] .')';
            }
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $formName = isset($data['changeUserConfirm']) ? 'TmMergeConfirmation' : 'TransportManagerMerge';
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm($formName);
        $form->setData($data);
        $formHelper->setFormActionFromRequest($form, $request);
        $form->get('toTmId')->setAttribute('data-lookup-url', $this->url()->fromRoute('transport-manager-lookup'));

        if ($request->isPost() && $form->isValid()) {
            $toTmId = (int) $form->getData()['toTmId'];
            $params = [
                'id' => $transportManagerId,
                'recipientTransportManager' => $toTmId
            ];
            if (isset($data['changeUserConfirm'])) {
                $params['confirm'] = true;
            }
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Tm\Merge::create($params)
            );

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('form.tm-merge.success');
                return $this->redirect()->toRouteAjax('transport-manager', ['transportManager' => $transportManagerId]);
            } else {
                $form = $this->processMergeFormMessages($response, $form, $toTmId);
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('tm-merge');

        $view = new \Zend\View\Model\ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Merge transport manager');
    }

    /**
     * Process TM merge form messages
     *
     * @param \Common\Service\Cqrs\Response $response
     * @param \Zend\Form\FormInterface $form
     * @param int $toTmId
     *
     * return Form
     */
    protected function processMergeFormMessages($response, $form, $toTmId)
    {
        $formMessages = [];

        if ($response->isNotFound()) {
            $formMessages['toTmId'][] = 'form.tm-merge.to-tm-id.validation.not-found';
            $form->setMessages($formMessages);
            return $form;
        }
        $result = $response->getResult();

        if (isset($result['messages']) && !isset($result['messages']['TM_MERGE_BOTH_HAVE_USER_ACCOUNTS'])) {
            foreach (array_keys($result['messages']) as $key) {
                $formMessages['toTmId'][] = 'form.tm-merge.to-tm-id.validation.' . $key;
            }
            $form->setMessages($formMessages);
        } elseif (isset($result['messages']['TM_MERGE_BOTH_HAVE_USER_ACCOUNTS'])) {
            $form = $this->setupMergeConfirmationForm($toTmId);
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addErrorMessage('unknown-error');
        }
        return $form;
    }

    /**
     * Setup TM merge confirmation form
     *
     * @param $toTmId
     * @return \Zend\Form\FormInterface
     */
    protected function setupMergeConfirmationForm($toTmId)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('TmMergeConfirmation');
        $form->get('messages')->get('message')->setValue('internal.confirm-merge.message');
        $form->get('form-actions')->get('submit')->setLabel('internal.confirm-merge.button');
        $form->get('toTmId')->setValue($toTmId);
        $form->get('changeUserConfirm')->setValue('Y');
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Unmerge a transport manager
     */
    public function unmergeAction()
    {
        $transportManagerId = (int) $this->params()->fromRoute('transportManager');
        $tmData = $this->getTransportManager($transportManagerId);
        if (!$tmData) {
            return $this->notFoundAction();
        }

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('GenericConfirmation');
        $message = $this->getServiceLocator()->get('Helper\Translation')->translateReplace(
            'form.tm-unmerge.message',
            [
                $transportManagerId,
                $tmData['homeCd']['person']['forename'] .' '. $tmData['homeCd']['person']['familyName'],
                $tmData['mergeToTransportManager']['id'],
                $tmData['mergeToTransportManager']['homeCd']['person']['forename'] .' '.
                    $tmData['mergeToTransportManager']['homeCd']['person']['familyName'],
            ]
        );
        $form->get('messages')->get('message')->setValue($message);
        $form->get('form-actions')->get('submit')->setLabel('form.tm-unmerge.confirm.action');
        $formHelper->setFormActionFromRequest($form, $request);

        if ($request->isPost()) {
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Tm\Unmerge::create(['id' => $transportManagerId])
            );
            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('form.tm-unmerge.success');

                return $this->redirect()->toRouteAjax('transport-manager', ['transportManager' => $transportManagerId]);
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        $view = new \Zend\View\Model\ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Unmerge transport manager');
    }

    /**
     * Get TransportManager data
     *
     * @param int $id TransportManager ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getTransportManager($id)
    {
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(['id' => $id])
        );
        if ($response->isNotFound()) {
            return null;
        }

        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting TransportManager');
        }

        return $response->getResult();
    }

    /**
     * Ajax lookup of transport manager name
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function lookupAction()
    {
        $transportManagerId = (int) $this->params()->fromQuery('transportManager');
        if (!$transportManagerId) {
            $response = new \Zend\Http\Response();
            $response->setStatusCode(422);
            return $response;
        }
        $view = new \Zend\View\Model\JsonModel();

        $tmData = $this->getTransportManager($transportManagerId);
        if (!$tmData) {
            return $this->notFoundAction();
        }
        $name = $tmData['homeCd']['person']['forename'] .' '. $tmData['homeCd']['person']['familyName'];
        if (isset($tmData['users'][0])) {
            $name .= ' (associated user: '. $tmData['users'][0]['loginId'] .')';
        }
        $view->setVariables(
            [
                'id' => $tmData['id'],
                'name' => $name,
            ]
        );
        return $view;
    }

    public function canRemoveAction()
    {
        $messageFormat = '';
        $query = \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(
            [
                'id' => $this->params()->fromRoute('transportManager')
            ]
        );
        $response = $this->handleQuery($query);

        $messages = [];

        if ($response->getResult()['isDetached'] === false) {
            $messages[] = 'transport-manager-remove-not-detached-error';
        }

        if (is_array($response->getResult()['hasUsers'])) {
            $messages[] = 'transport-manager-remove-has-users-error';
        }

        if (count($messages) <= 0) {
            return $this->redirectToRoute(
                'transport-manager/remove',
                [
                    'transportManager' => $this->params()->fromRoute('transportManager')
                ]
            );
        }

        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest(
                'LicenceStatusDecisionMessages',
                $this->getRequest()
            );

        // foreach message set a format to work with FormElement
        foreach ($messages as $m) {
            $messageFormat .= '%s<br />';
        }

        $form->get('messages')->get('message')->setValue($messageFormat);
        $form->get('messages')->get('message')->setTokens($messages);
        $form->get('form-actions')->remove('continue');

        $view = $this->getViewWithTm(['form' => $form]);

        $view->setTemplate('pages/form');

        return $this->renderView($view, 'transport-manager-remove');
    }

    public function removeAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest('GenericConfirmation', $request);

        $form->get('messages')
            ->get('message')
            ->setValue('transport-manager-remove-are-you-sure');

        if ($request->isPost()) {
            $command = \Dvsa\Olcs\Transfer\Command\Tm\Remove::create(
                [
                    'id' => $this->params()->fromRoute('transportManager'),
                    'removedDate' => new \DateTime()
                ]
            );

            $response = $this->handleCommand($command);
            if ($response->isOk()) {
                $this->flashMessenger()->addSuccessMessage('transport-manager-removed');
                return $this->redirectToRouteAjax(
                    'transport-manager/details',
                    [
                        'transportManager' => $this->params()->fromRoute('transportManager')
                    ]
                );
            }
        }

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'transport-manager-remove');
    }

    public function undoDisqualificationAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest('GenericConfirmation', $request);

        $form->get('messages')
            ->get('message')
            ->setValue('transport-manager-undo-disqualification-are-you-sure');

        $form->get('form-actions')
            ->get('submit')
            ->setLabel('transport-manager-confirmation-remove-disqualification');

        if ($request->isPost()) {
            $command = \Dvsa\Olcs\Transfer\Command\Tm\UndoDisqualification::create(
                [
                    'id' => $this->params()->fromRoute('transportManager'),
                ]
            );

            $response = $this->handleCommand($command);
            if ($response->isOk()) {
                $this->flashMessenger()->addSuccessMessage('transport-manager-disqualification-removed');
                return $this->redirectToRouteAjax(
                    'transport-manager/details',
                    [
                        'transportManager' => $this->params()->fromRoute('transportManager')
                    ]
                );
            }
            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'transport-manager-confirmation-remove-disqualification');
    }

    /**
     * Sets the navigation to that specified in the controller. Useful for when a controller is
     * 100% represented by a single navigation object.
     */
    final public function setNavigationCurrentLocation()
    {
        if (empty($this->navigationId)) {
            return;
        }

        $navigation = $this->getServiceLocator()->get('Navigation');
        $navigation->findOneBy('id', $this->navigationId)->setActive();
    }

    /**
     * @codeCoverageIgnore this is part of the event system.
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        if (! empty($this->navigationId)) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, [$this, 'setNavigationCurrentLocation'], 6);
        }
    }
}
