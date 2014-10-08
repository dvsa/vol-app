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
        // @TODO Need to ensure the application is a variation

        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->goToOverview($applicationId);
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $typeOfLicenceData = $this->getTypeOfLicenceData();

            $data = array(
                'version' => $typeOfLicenceData['version'],
                'type-of-licence' => array(
                    'operator-location' => $typeOfLicenceData['niFlag'],
                    'operator-type' => $typeOfLicenceData['goodsOrPsv'],
                    'licence-type' => $typeOfLicenceData['licenceType']
                )
            );
        }

        $form->setData($data);

        if ($request->isPost() && $form->isValid()) {

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
