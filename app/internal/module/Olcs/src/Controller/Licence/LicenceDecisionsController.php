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

    /**
     * IMO this action should be responsible for performing the active check logic and should then forward
     * onto the relevant action.
     */
    public function activeLicenceCheckAction()
    {

    }

    public function curtailAction()
    {
        $licenceId = $this->fromRoute('licence');
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        if ($licenceStatusHelper->isLicenceCurtailable($licenceId)) {
            if ($this->getRequest()->isPost()) {
                $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');

                $postData = $this->request->getPost();

                if ($this->isButtonPressed('cancel')) {
                    return $this->redirectToRoute(
                        'licence',
                        array(
                            'licence' => $licenceId
                        )
                    );
                } elseif (isset($postData['licence-decision-curtail-now']['curtailNow'])) {
                    $licenceStatusHelper->curtailNow($licenceId);
                    $this->flashMessenger()->addSuccessMessage('The curtailment details have been saved');
                    return $this->redirectToRouteAjax(
                        'licence',
                        array(
                            'licence' => $licenceId
                        )
                    );
                }

                $form = $formHelper->createForm('LicenceStatusDecisionCurtail');
                $form->setAttribute(
                    'action',
                    $this->getUrlFromRoute(
                        'licence/curtail-licence',
                        array(
                            'licence' => $licenceId
                        )
                    )
                );

                if (isset($postData['licence-decision-curtail']['form-actions']['curtailSave'])) {
                    $form->setData((array)$this->request->getPost());

                    if ($form->isValid()) {
                        $licenceStatusEntityService->createStatusForLicence(
                            $licenceId,
                            array(
                                'data' => array(
                                    'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_CURTAILED,
                                    'startDate' => $form->getInputFilter()
                                        ->get('licence-decision-curtail')
                                        ->get('curtailFrom')
                                        ->getValue(),
                                    'endDate' => $form->getInputFilter()
                                        ->get('licence-decision-curtail')
                                        ->get('curtailTo')
                                        ->getValue()
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

            $form = $formHelper->createForm('LicenceStatusDecisionMessages');
            $form->setAttribute(
                'action',
                $this->getUrlFromRoute(
                    'licence/curtail-licence',
                    array(
                        'licence' => $licenceId
                    )
                )
            );

            $messages = array_map(
                function ($message) use ($translator) {
                    return $translator->translate($message['message']);
                },
                $licenceStatusHelper->getMessages()
            );

            $form->get('messages')->get('message')->setValue(implode('<br>', $messages));

            $view = $this->getViewWithLicence(
                array(
                    'form' => $form
                )
            );

            $view->setTemplate('partials/form');

            return $this->renderView($view);
        }
    }
}
