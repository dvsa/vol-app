<?php

namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractUndertakingsController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

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
     * @param \Common\Form\Form $form            form
     * @param array             $applicationData application data
     *
     * @return void
     */
    protected function updateForm($form, $applicationData)
    {
        parent::updateForm($form, $applicationData);

        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        if ($goodsOrPsv !== Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'interim');
        }

        $this->updateSubmitButtons($form, $applicationData);
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
