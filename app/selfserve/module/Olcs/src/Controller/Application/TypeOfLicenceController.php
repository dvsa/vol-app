<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractApplicationController
{
    /**
     * Type of licence section
     */
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('id');

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $request = $this->getRequest();

        $form = $this->getHelperService('FormHelper')->createForm('Lva\TypeOfLicence');

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);

            if ($form->isValid()) {

                $licenceId = $this->getLicenceId($applicationId);

                // Process data

                // Save data

                // Redirect
            }
        } else {
            $licenceId = $this->getLicenceId($applicationId);
            $typeOfLicenceData = $this->getEntityService('Licence')->getTypeOfLicenceData($licenceId);

            $form->setData(
                array(
                    'version' => $typeOfLicenceData['version'],
                    'type-of-licence' => array(
                        'operator-location' => $typeOfLicenceData['niFlag'],
                        'operator-type' => $typeOfLicenceData['goodsOrPsv'],
                        'licence-type' => $typeOfLicenceData['licenceType']
                    )
                )
            );
        }

        return new Section(
            [
                'title' => 'Type of licence',
                'form' => $form
            ]
        );
    }
}
