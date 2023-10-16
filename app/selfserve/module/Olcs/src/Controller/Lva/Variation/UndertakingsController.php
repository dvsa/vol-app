<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Form\Form;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractUndertakingsController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UndertakingsController extends AbstractUndertakingsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';

    protected TranslationHelperService $translationHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param ScriptFactory $scriptFactory
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService $formHelper
     * @param TranslationHelperService $translationHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        ScriptFactory $scriptFactory,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        TranslationHelperService $translationHelper
    ) {
        $this->translationHelper = $translationHelper;

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
     * Get form
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        return $this->formHelper
            ->createForm('Lva\VariationUndertakings');
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
        $translator = $this->translationHelper;
        $fieldset = $form->get('declarationsAndUndertakings');
        $formHelper = $this->formHelper;

        $summaryDownload = $translator->translateReplace(
            'undertakings_summary_download',
            [
                $this->url()->fromRoute('lva-' . $this->lva . '/review', [], [], true),
                $translator->translate('view-full-application'),
            ]
        );

        $fieldset->get('summaryDownload')->setAttribute('value', $summaryDownload);

        $form->get('interim')->get('YContent')->get('interimFee')->setValue(
            $translator->translateReplace('selfserve.declaration.interim_fee', [$applicationData['interimFee']])
        );

        // if interimFee is null
        if (!$applicationData['interimFee']) {
            // remove the block that displays it
            $form->get('interim')->get('YContent')->remove('interimFee');
        }
        // if this application can't have an interim license
        if (!$applicationData['canHaveInterimLicence']) {
            // completely remove the interim option
            $formHelper->remove($form, 'interim');
        }

        $formHelper->remove($form, 'form-actions->sign');
        $formHelper->remove($form, 'form-actions->saveAndContinue');
        $this->updateSubmitButtons($form, $applicationData);

        return $form;
    }

    /**
     * Is ready to submit
     *
     * @param array $applicationData varuation data
     *
     * @return bool
     */
    protected function isReadyToSubmit($applicationData)
    {
        $sections = $this->getVariationSections($applicationData);

        $updated = 0;
        foreach ($sections as $key => $section) {
            if ($key === RefData::UNDERTAKINGS_KEY) {
                continue;
            }

            if ($section['status'] === RefData::VARIATION_STATUS_REQUIRES_ATTENTION) {
                return false;
            }

            if ($section['status'] === RefData::VARIATION_STATUS_UPDATED) {
                $updated++;
            }
        }
        return ($updated > 0);
    }
}
