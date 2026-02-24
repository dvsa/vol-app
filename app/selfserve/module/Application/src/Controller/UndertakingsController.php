<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Form\Form;
use Common\Module;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractUndertakingsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

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
    protected string $location  = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param ScriptFactory $scriptFactory
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService $formHelper
     * @param TranslationHelperService $translationHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        ScriptFactory $scriptFactory,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        protected TranslationHelperService $translationHelper,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $scriptFactory,
            $transferAnnotationBuilder,
            $commandService,
            $flashMessengerHelper,
            $formHelper
        );
    }



    /**
     * View Declarations page
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        // Get signature details from backend
        $applicationData = $this->getUndertakingsData();
        // If application has digital signature then show digital signature page
        $signed = !empty($applicationData['signature']['name']) && !empty($applicationData['signature']['date']);
        if ($signed) {
            return $this->signedAction();
        }

        return parent::indexAction();
    }

    /**
     * Shows Declaration page after being signed by GDS Verify
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function signedAction()
    {
        $form = $this->formHelper->createForm('Lva\ApplicationSigned');

        // If form submitted then go to payment page
        if ($this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(
                'lva-' . $this->lva . '/pay-and-submit',
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
        $translator = $this->translationHelper;
        $form->get('signatureDetails')->get('signature')->setValue(
            $translator->translateReplace('undertakings_signed', [$signedBy, $signedDate->format(Module::$dateFormat)])
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
        return $this->formHelper
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
        $translator = $this->translationHelper;
        $formHelper = $this->formHelper;

        $this->updateReviewElement($applicationData, $fieldset, $translator);
        $this->updateDeclarationElement($fieldset, $translator);
        $this->updateInterimFieldset($form, $applicationData);
        $this->updateSubmitButtons($form, $applicationData);
        $this->updateFormBasedOnDisableSignatureSetting($form);
        $this->updateInterimFee($form, $applicationData, $translator);
        $this->updateGoodsApplicationInterim($form, $applicationData, $translator);
        if (!$applicationData['canHaveInterimLicence'] && $form->has('interim')) {
            $formHelper->remove($form, 'interim');
        }

        return $form;
    }

    /**
     * Update review fieldset
     *
     * @param array                                           $applicationData application data
     * @param \Laminas\Form\Fieldset                             $fieldset        fieldset
     * @param \Common\Service\Helper\TranslationHelperService $translator      translator
     *
     * @return void
     */
    protected function updateReviewElement($applicationData, $fieldset, $translator)
    {
        $person = match ($applicationData['licence']['organisation']['type']['id']) {
            RefData::ORG_TYPE_SOLE_TRADER => 'application.review-declarations.review.business-owner',
            RefData::ORG_TYPE_OTHER => 'application.review-declarations.review.person',
            RefData::ORG_TYPE_PARTNERSHIP => 'application.review-declarations.review.partner',
            RefData::ORG_TYPE_REGISTERED_COMPANY, RefData::ORG_TYPE_LLP => 'application.review-declarations.review.director',
            default => 'application.review-declarations.review.director',
        };

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
     * @param \Laminas\Form\Fieldset                             $fieldset   fieldset
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
        if (!$form->has('interim')) {
            return;
        }
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        if ($goodsOrPsv !== RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->formHelper->remove($form, 'interim');
        }
    }

    /**
     * Update interim fee value
     *
     * @param Form                                            $form            form
     * @param array                                           $applicationData application data
     * @param \Common\Service\Helper\TranslationHelperService $translator      translator
     *
     * @return void
     */
    protected function updateInterimFee($form, $applicationData, $translator)
    {
        if (!$form->has('interim')) {
            return;
        }
        $form->get('interim')->get('YContent')->get('interimFee')->setValue(
            $translator->translateReplace('selfserve.declaration.interim_fee', [$applicationData['interimFee']])
        );
        if (!$applicationData['interimFee']) {
            $form->get('interim')->get('YContent')->remove('interimFee');
        }
    }

    /**
     * Update Goods Application Interim Label based on interim fee value
     *
     * @param Form                                            $form            form
     * @param array                                           $applicationData application data
     * @param \Common\Service\Helper\TranslationHelperService $translator      translator
     *
     * @return void
     */
    protected function updateGoodsApplicationInterim($form, $applicationData, $translator)
    {
        if (!$form->has('interim')) {
            return;
        }
        if (!$applicationData['interimFee']) {
            $form->get('interim')->get('goodsApplicationInterim')->setLabel(
                $translator->translate('interim.application.undertakings.form.checkbox.label.no-interim-fee')
            );
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
        $formHelper = $this->formHelper;
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
