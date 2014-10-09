<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Olcs\Controller\Lva\Traits\TypeOfLicenceTrait;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractVariationController
{
    use TypeOfLicenceTrait;

    /**
     * Type of licence section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $this->formatDataForForm($this->getTypeOfLicenceData());
        }

        $form = $this->getTypeOfLicenceForm()->setData($data);

        if ($request->isPost() && $form->isValid()) {

            $applicationId = $this->getApplicationId();

            $licenceId = $this->getLicenceId($applicationId);

            $data = $this->formatDataForSave($data);

            $data['id'] = $licenceId;

            $this->getEntityService('Licence')->save($data);

            if ($this->isButtonPressed('saveAndContinue')) {
                return $this->goToNextSection('type_of_licence');
            }

            return $this->goToOverview($applicationId);
        }

        return $this->getSectionView($form);
    }
}
