<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;
use Olcs\Controller\Lva\AbstractUndertakingsController;
use Common\RefData;
use Common\Form\Form;

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
    protected $location = 'external';

    /**
     * Get form
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
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
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $fieldset = $form->get('declarationsAndUndertakings');
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $summaryDownload = $translator->translateReplace(
            'undertakings_summary_download',
            [
                $this->url()->fromRoute('lva-' . $this->lva . '/review', [], [], true),
                $translator->translate('view-full-application'),
            ]
        );

        $fieldset->get('summaryDownload')->setAttribute('value', $summaryDownload);
        if (!$applicationData['canHaveInterimLicence']) {
            $formHelper->remove($form, 'interim');
        } else {
            $form->get('interim')->get('interimFee')->setValue(
                $translator->translateReplace('selfserve.declaration.interim_fee', $applicationData['interimFee'])
            );
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
