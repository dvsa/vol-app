<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Form\Elements\InputFilters\SelectEmpty as SelectElement;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewController extends AbstractController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Application overview
     */
    public function indexAction()
    {
        $form = $this->getTrackingForm();
        $this->alterForm($form);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();

            if ($this->isButtonPressed('cancel')) {
                $this->addSuccessMessage('Any changes have been discarded');
                return $this->reload();
            }

            $form->setData($data);
            if ($form->isValid()) {

                $this->save($data);

                $this->addSuccessMessage('The overview page has been saved');

                if ($this->isButtonPressed('saveAndContinue')) {
                     return $this->redirect()
                        ->toRoute(
                            'lva-application/type_of_licence',
                            ['application' => $this->getApplicationId()]
                        );
                }

                return $this->reload();
            }

        } else {
            $trackingData = $this->getTrackingDataForApplication($this->getApplicationId());
            $form->setData($this->formatTrackingDataForForm($trackingData));
        }

        return $this->render('overview', $form, []);
    }

    protected function getTrackingForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('ApplicationOverview');
    }

    protected function formatTrackingDataForForm($data)
    {
        return ['tracking' => $data];
    }

    protected function getTrackingDataForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->getTrackingStatuses($applicationId);
    }

    protected function alterForm($form)
    {
        $fieldset = $form->get('tracking');

        $stringHelper = $this->getServiceLocator()->get('Helper\String');

        // build up the tracking fieldset dynamically, based on relevant sections
        $sections = $this->getAccessibleSections();
        $options  = $this->getServiceLocator()->get('Entity\ApplicationTracking')->getValueOptions();
        foreach ($sections as $section) {
            $selectProperty = lcfirst($stringHelper->underscoreToCamel($section)) . 'Status';
            $select = new SelectElement($selectProperty);
            $select->setValueOptions($options);
            $select->setEmptyOption('');
            $select->setLabel('section.name.'.$section);
            $fieldset->add($select);
        }

        // modify label (it should be 'Save' not 'Save & return' as per AC)
        $form->get('form-actions')->get('save')->setLabel('Save');

        return $form;
    }

    /**
     * Save tracking data
     *
     * @param array $data
     */
    protected function save($data)
    {
        $trackingData = $data['tracking'];

        // nullify empty fields, avoids them becoming 0
        foreach ($trackingData as $key => $value) {
            if ($value == '') {
                unset($trackingData[$key]);
            }
        }

        return $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->save($trackingData);
    }
}
