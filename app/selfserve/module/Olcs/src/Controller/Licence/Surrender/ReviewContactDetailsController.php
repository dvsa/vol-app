<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\Licence\Surrender\ReviewContactDetails;
use Common\Service\Helper\TranslationHelperService;

class ReviewContactDetailsController extends AbstractSurrenderController
{

    public function indexAction()
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        $params = [
            'title' => 'licence.surrender.review_contact_details.title',
            'licNo' => $this->licence['licNo'],
            'content' => 'licence.surrender.review_contact_details.content',
            'note' => 'licence.surrender.review_contact_details.note',
            'form' => $this->getConfirmationForm(),
            'backLink' => $this->getBackLink('lva-licence'),
            'sections' => ReviewContactDetails::makeSections($this->licence, $this->url(), $translator)
        ];

        return $this->renderView($params);
    }

    public function confirmAction()
    {
        // here we should change the status and redirect to next step
    }

    private function getConfirmationForm(): \Common\Form\Form
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'licence/surrender/review-contact-details',
                [
                    'action' => 'confirm',
                    'licence' => $this->licenceId,
                    'surrender' => $this->surrenderId
                ]
            )
        );
        $submitLabel = $translator->translate('approve-details');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }
}
