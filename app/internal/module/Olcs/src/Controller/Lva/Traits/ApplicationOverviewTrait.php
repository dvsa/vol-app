<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Form\Elements\InputFilters\SelectEmpty as SelectElement;
use Zend\View\Model\ViewModel;

/**
 * This trait enables the Application and Variation overview controllers to
 * share identical behaviour
 */
trait ApplicationOverviewTrait
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        $applicationId = $this->getIdentifier();
        $application = null;

        $form = $this->getOverviewForm();
        $this->alterForm($form);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();

            if ($this->isButtonPressed('cancel')) {
                $this->addSuccessMessage('flash-discarded-changes');
                return $this->reload();
            }

            $form->setData($data);
            if ($form->isValid()) {

                $response = $this->getServiceLocator()->get('BusinessServiceManager')
                    ->get('Lva\ApplicationOverview')
                    ->process($form->getData());

                if ($response->isOk()) {
                    $this->addSuccessMessage('application.overview.saved');
                } else {
                    $this->addErrorMessage('application.overview.save.failed');
                }

                if ($this->isButtonPressed('saveAndContinue')) {
                     return $this->redirect()
                        ->toRoute(
                            'lva-'.$this->lva.'/type_of_licence',
                            ['application' => $applicationId]
                        );
                }

                return $this->reload();
            }




        } else {
            $application = $this->getServiceLocator()->get('Entity\Application')->getOverview($applicationId);
            $tracking = $this->getTrackingDataForApplication($applicationId);
            $formData = $this->formatDataForForm($application, $tracking);
            $form->setData($formData);
        }

        if (is_null($application)) {
            $application = $this->getServiceLocator()->get('Entity\Application')->getOverview($applicationId);
        }

        $licenceId = $application['licence']['id'];
        $licence = $this->getServiceLocator()->get('Entity\Licence')->getExtendedOverview($licenceId);

        // Render the view
        $viewData = $this->getServiceLocator()->get('Helper\ApplicationOverview')
            ->getViewData($application, $licence, $this->lva);
        $content = new ViewModel(
            array_merge(
                $viewData,
                ['form' => $form]
            )
        );
        $content->setTemplate('pages/application/overview');

        return $this->render($content);
    }

    protected function getOverviewForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('ApplicationOverview');
    }

    /**
     * @param array $application application overview data
     * @param array $tracking tracking status data
     * @return array
     */
    protected function formatDataForForm($application, $tracking)
    {
        return [
            'details' => [
                'receivedDate'         => $application['receivedDate'],
                'targetCompletionDate' => $application['targetCompletionDate'],
                'leadTcArea'           => $application['licence']['organisation']['leadTcArea']['id'],
                'version'              => $application['version'],
                'id'                   => $application['id'],
            ],
            'tracking' => $tracking,
        ];
    }

    /**
     * @param int $applicationId
     * @return array
     */
    protected function getTrackingDataForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->getTrackingStatuses($applicationId);
    }

    protected function alterForm($form)
    {
        // @TODO conditional display of TC area

        $form->get('details')->get('leadTcArea')->setValueOptions(
            $this->getServiceLocator()->get('Entity\TrafficArea')->getValueOptions()
        );

        $fieldset = $form->get('tracking');

        $stringHelper = $this->getServiceLocator()->get('Helper\String');

        // build up the tracking fieldset dynamically, based on relevant sections
        $sections = $this->getAccessibleSections();
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
}
