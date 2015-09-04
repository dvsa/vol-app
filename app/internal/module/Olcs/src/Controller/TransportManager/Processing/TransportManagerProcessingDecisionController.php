<?php

/**
 * Transport Manager Processing Decision Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Dvsa\Olcs\Transfer\Command\Tm;
use Dvsa\Olcs\Transfer\Query\Tm\TransportManager;
use Olcs\Controller\TransportManager\Processing\AbstractTransportManagerProcessingController;

/**
 * Transport Manager Processing Decision Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerProcessingDecisionController extends AbstractTransportManagerProcessingController
{
    /**
     * @var string
     */
    protected $section = 'processing-decisions';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('pages/placeholder');
        return $this->renderView($view);
    }

    public function canRemoveAction()
    {
        $query = TransportManager::create(
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
            $messages[] = 'transport-manager-remove-has-users-error' . implode(', ', $response->getResult()['hasUsers']);
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

        $form->get('messages')
            ->get('message')
            ->setValue(
                implode('<br />', $messages)
            );

        $form->get('form-actions')->remove('continue');

        $view = $this->getViewWithTm(
            [
                'form' => $form
            ]
        );
        $view->setTemplate('partials/form');
        return $this->renderView($view, 'transport-manager-remove');
    }

    public function removeAction()
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest('GenericConfirmation', $request);

        $form->get('messages')
            ->get('message')
            ->setValue('transport-manager-remove-are-you-sure');

        if ($request->isPost()) {
            $command = Tm\Remove::create(
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

        $view = $this->getViewWithTm(
            [
                'form' => $form
            ]
        );
        $view->setTemplate('partials/form');
        return $this->renderView($view, 'transport-manager-remove');
    }
}
