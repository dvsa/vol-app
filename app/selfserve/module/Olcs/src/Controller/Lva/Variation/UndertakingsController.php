<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;
use Olcs\Controller\Lva\AbstractUndertakingsController;
use Common\RefData;

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
     * @param \Common\Form\Form $form            form
     * @param array             $applicationData application data
     *
     * @return void
     */
    protected function updateForm($form, $applicationData)
    {
        parent::updateForm($form, $applicationData);

        if (!$applicationData['canHaveInterimLicence']) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'interim');
        }

        $this->updateSubmitButtons($form, $applicationData);
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
