<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Form\Elements\InputFilters\SelectEmpty as SelectElement;

/**
 * This trait enables the Application and Variation overview controllers to
 * share identical behaviour
 */
trait ApplicationTrackingTrait
{
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
                $this->addSuccessMessage('flash-discarded-changes');
                return $this->reload();
            }

            $form->setData($data);
            if ($form->isValid()) {

                $this->save($data);

                $this->addSuccessMessage('application.overview.saved');

                if ($this->isButtonPressed('saveAndContinue')) {
                     return $this->redirect()
                        ->toRoute(
                            'lva-'.$this->lva.'/type_of_licence',
                            [$this->getIdentifierIndex() => $this->getIdentifier()]
                        );
                }

                return $this->reload();
            }

        } else {
            $trackingData = $this->getTrackingDataForApplication($this->getIdentifier());
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
        $sections = $this->getSections();
        $options  = $this->getServiceLocator()->get('Entity\ApplicationTracking')->getValueOptions();
        foreach ($sections as $section) {
            $selectProperty = lcfirst($stringHelper->underscoreToCamel($section)) . 'Status';
            $select = new SelectElement($selectProperty);
            $select->setValueOptions($options);
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

        return $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->save($trackingData);
    }

    protected function getSections()
    {
        $sections = $this->getAccessibleSections();

        // 'undertakings' (Review and Declarations) isn't accessible in the config
        // but AC require it is shown
        $sections[] = 'undertakings';

        return $sections;
    }
}
