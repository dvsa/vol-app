<?php

/**
 * LicenceDecisionsController.php
 */
namespace Olcs\Controller\Licence;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use Olcs\Controller\AbstractController;

use Olcs\Controller\Traits\LicenceControllerTrait;

/**
 * Class LicenceDecisionsController
 *
 * Calling code for logic around actions directly against the licence. E.g.
 * suspending or revoking the licence for a specified amount of time.
 *
 * @package Olcs\Controller\Licence
 */
class LicenceDecisionsController extends AbstractController
{
    use LicenceControllerTrait;

    public function activeLicenceCheckAction()
    {
        $decision = $this->fromRoute('decision', null);
        $licence = $this->fromRoute('licence', null);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        $form = $formHelper->createFormWithRequest('LicenceStatusDecisionMessages', $this->getRequest());

        $messages = array_map(
            function ($message) use ($translator) {
                return $translator->translate($message['message']);
            },
            $licenceStatusHelper->isLicenceActive($licence)
        );

        $form->get('messages')->get('message')->setValue(implode('<br>', $messages));

        switch ($decision) {
            case 'curtail':
                if ($this->getRequest()->isPost() || empty($messages)) {
                    return $this->redirectToRoute(
                        'licence/curtail-licence',
                        array(
                            'licence' => $licence
                        )
                    );
                }
                break;
            case 'revoke':
                if(empty($messages)) {
                    return $this->redirectToRoute(
                        'licence/revoke-licence',
                        array(
                            'licence' => $licence
                        )
                    );
                }
        }

        $view = $this->getViewWithLicence(
            array(
                'form' => $form
            )
        );

        $view->setTemplate('partials/form');

        return $this->renderView($view);
    }

    public function curtailAction()
    {
        $licenceId = $this->fromRoute('licence');
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        if ($this->isButtonPressed('curtailNow')) {
            $licenceStatusHelper->curtailNow($licenceId);
            $this->flashMessenger()->addSuccessMessage('The curtailment details have been saved');
            return $this->redirectToRouteAjax(
                'licence',
                array(
                    'licence' => $licenceId
                )
            );
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('LicenceStatusDecisionCurtail', $this->getRequest());

        if ($this->request->isPost()) {
            $form->setData((array)$this->request->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
                $licenceStatusEntityService->createStatusForLicence(
                    $licenceId,
                    array(
                        'data' => array(
                            'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_CURTAILED,
                            'startDate' => $formData['licence-decision-curtail']['curtailFrom'],
                            'endDate' => $formData['licence-decision-curtail']['curtailTo'],
                        )
                    )
                );

                $this->flashMessenger()->addSuccessMessage('The curtailment details have been saved');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        $view = $this->getViewWithLicence(
            array(
                'form' => $form
            )
        );

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/licence-curtail']);

        $view->setTemplate('partials/form');

        return $this->renderView($view);
    }

    public function revokeAction()
    {
        $licenceId = $this->fromRoute('licence');
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        if ($this->isButtonPressed('curtailNow')) {
            $licenceStatusHelper->curtailNow($licenceId);
            $this->flashMessenger()->addSuccessMessage('The curtailment details have been saved');
            return $this->redirectToRouteAjax(
                'licence',
                array(
                    'licence' => $licenceId
                )
            );
        }
    }
}
