<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\Lva\Traits\TypeOfLicenceTrait;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractLicenceController
{
    use TypeOfLicenceTrait;

    /**
     * Type of licence section
     */
    public function indexAction()
    {
        $licenceId = $this->getLicenceId();

        if ($this->isButtonPressed('cancel')) {
            return $this->goToOverview($licenceId);
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();
        $form->get('form-actions')->remove('saveAndContinue');

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

            $data = $this->formatDataForSave($data);

            $data['id'] = $licenceId;

            $this->getEntityService('Licence')->save($data);

            if ($this->isButtonPressed('saveAndContinue')) {
                return $this->goToNextSection('type_of_licence');
            }

            return $this->goToOverview($licenceId);
        }

        return $this->getSectionView($form);
    }
}
