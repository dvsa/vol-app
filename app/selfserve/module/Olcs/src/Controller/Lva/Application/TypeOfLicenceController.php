<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Zend\Form\Form;
use Common\View\Model\Section;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends Lva\AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait;

    protected $location = 'external';
    protected $lva = 'application';

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function renderCreateApplication($titleSuffix, Form $form = null)
    {
        return new Section(array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form));
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute('dashboard');
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();
        $form->get('form-actions')->remove('saveAndContinue')
            ->get('save')->setLabel('continue.button')->setAttribute('class', 'action--primary large');

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            if ($form->isValid()) {

                $organisationId = $this->getCurrentOrganisationId();
                $ids = $this->getServiceLocator()->get('Entity\Application')->createNew($organisationId);

                $data = $this->formatDataForSave($data);

                $data['id'] = $ids['application'];
                $data['version'] = 1;

                $this->getServiceLocator()->get('Entity\Application')->save($data);

                $this->updateCompletionStatuses($ids['application'], 'type_of_licence');

                $adapter = $this->getTypeOfLicenceAdapter();
                $adapter->createFee($ids['application']);

                return $this->goToOverview($ids['application']);
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('type-of-licence');

        return $this->renderCreateApplication('type_of_licence', $form);
    }
}
