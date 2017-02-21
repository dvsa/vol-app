<?php

namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractUndertakingsController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;
use Common\Form\Form;

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UndertakingsController extends AbstractUndertakingsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * View Declarations page
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * Shows Declaration page after being signed by GDS Verify
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function signedAction()
    {
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('Lva\ApplicationSigned');

        // If form submitted then go to payment page
        if ($this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(
                'lva-'.$this->lva . '/pay-and-submit',
                [$this->getIdentifierIndex() => $this->getIdentifier(), 'redirect-back' => 'undertakings'],
                [],
                true
            );
        }

        // Get signature details from backend
        $applicationData = $this->getUndertakingsData();
        $signedBy = $applicationData['signature']['name'];
        $signedDate = new \DateTime($applicationData['signature']['date']);

        // Update the form HTML with details name of person who signed
        /** @var \Common\Service\Helper\TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $form->get('signatureDetails')->get('signature')->setValue(
            $translator->translateReplace('undertakings_signed', [$signedBy, $signedDate->format(\DATE_FORMAT)])
        );

        return $this->render('undertakings', $form);
    }

    /**
     * Get form
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\ApplicationUndertakings');
    }

    /**
     * Update form
     *
     * @param Form  $form            form
     * @param array $applicationData application data
     *
     * @return Form
     */
    protected function updateForm($form, $applicationData)
    {
        $fieldset = $form->get('declarationsAndUndertakings');
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        $this->updateReviewElement($applicationData, $fieldset, $translator);
        $this->updateDeclarationElement($fieldset, $translator);
        $this->updateInterimFieldset($form, $applicationData);
        $this->updateSubmitButtons($form, $applicationData);
        $this->updateFormBasedOnDisableSignatureSetting($form);

        return $form;
    }

    /**
     * Update review fieldset
     *
     * @param array                                           $applicationData application data
     * @param \Zend\Form\Fieldset                             $fieldset        fieldset
     * @param \Common\Service\Helper\TranslationHelperService $translator      translator
     *
     * @return void
     */
    protected function updateReviewElement($applicationData, $fieldset, $translator)
    {
        switch ($applicationData['licence']['organisation']['type']['id']) {
            case RefData::ORG_TYPE_SOLE_TRADER:
                $person = 'application.review-declarations.review.business-owner';
                break;
            case RefData::ORG_TYPE_OTHER:
                $person = 'application.review-declarations.review.person';
                break;
            case RefData::ORG_TYPE_PARTNERSHIP:
                $person = 'application.review-declarations.review.partner';
                break;
            case RefData::ORG_TYPE_REGISTERED_COMPANY:
            case RefData::ORG_TYPE_LLP:
                $person = 'application.review-declarations.review.director';
                break;
            default:
                $person = 'application.review-declarations.review.director';
                break;
        }

        $reviewElement = $fieldset->get('review');
        $reviewText = $translator->translateReplace(
            'markup-review-text',
            [
                $translator->translate($person),
                $this->url()->fromRoute('lva-' . $this->lva . '/review', [], [], true)
            ]
        );
        $reviewElement->setAttribute('value', $reviewText);
    }

    /**
     * Update declaration element
     *
     * @param \Zend\Form\Fieldset                             $fieldset   fieldset
     * @param \Common\Service\Helper\TranslationHelperService $translator translator
     *
     * @return void
     */
    protected function updateDeclarationElement($fieldset, $translator)
    {
        $fieldset->get('declaration')->setValue($this->data['declarations']);
        $fieldset->get('declaration')->setAttribute('class', 'guidance');

        $declarationDownload = $translator->translateReplace(
            'undertakings_declaration_download',
            [
                $this->url()->fromRoute('lva-' . $this->lva . '/declaration', [], [], true),
                $translator->translate('print-declaration-form'),
            ]
        );

        $fieldset->get('declarationDownload')->setAttribute('value', $declarationDownload);
    }

    /**
     * Update interim fieldset
     *
     * @param Form  $form            form
     * @param array $applicationData application data
     *
     * @return void
     */
    protected function updateInterimFieldset($form, $applicationData)
    {
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        if ($goodsOrPsv !== Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'interim');
        }
    }

    /**
     * Update form based on disable signature setting
     *
     * @param Form $form form
     *
     * @return void
     */
    protected function updateFormBasedOnDisableSignatureSetting($form)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        if ($this->data['disableSignatures']) {
            // remove options radio, sign button, checkbox, enable print sign and return fieldset
            $formHelper->remove($form, 'declarationsAndUndertakings->signatureOptions');
            $formHelper->remove($form, 'declarationsAndUndertakings->declarationForVerify');
            $formHelper->remove($form, 'form-actions->sign');
        } else {
            $formHelper->remove($form, 'declarationsAndUndertakings->disabledReview');
            $data = (array) $this->getRequest()->getPost();
            if (
                isset($data['declarationsAndUndertakings']['signatureOptions'])
                && $data['declarationsAndUndertakings']['signatureOptions'] === 'N'
            ) {
                $formHelper->remove($form, 'declarationsAndUndertakings->declarationForVerify');
            }
        }
    }

    /**
     * Is application ready to submit
     *
     * @param array $applicationData application data
     *
     * @return bool
     */
    protected function isReadyToSubmit($applicationData)
    {
        $sections = $this->setEnabledAndCompleteFlagOnSections(
            $applicationData['sections'],
            $applicationData['applicationCompletion']
        );
        foreach ($sections as $key => $section) {
            if ($section['enabled'] && !$section['complete'] && $key !== RefData::UNDERTAKINGS_KEY) {
                return false;
            }
        }
        return true;
    }
}
