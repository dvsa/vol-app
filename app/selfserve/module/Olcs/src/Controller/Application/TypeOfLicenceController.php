<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

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
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();

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

        return $this->getSectionView($form);
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);

            if ($form->isValid()) {

                // Create licence

                // Create application

                // Process data

                // Save data

                // Redirect
            }
        }

        return $this->getSectionView($form);
    }

    /**
     * Get type of licence form
     *
     * @return \Zend\Form\Form
     */
    private function getTypeOfLicenceForm()
    {
        return $this->getHelperService('FormHelper')->createForm('Lva\TypeOfLicence');
    }

    /**
     * Get section view
     *
     * @param \Zend\Form\Form $form
     * @return Section
     */
    private function getSectionView(Form $form)
    {
        return new Section(
            [
                'title' => 'Type of licence',
                'form' => $form
            ]
        );
    }
}
