<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Form\Elements\InputFilters\SelectEmpty as SelectElement;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\LicenceEntityService as Licence;

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

        // Render the view
        $content = new ViewModel(
            array_merge(
                ['multiItems' => $this->getOverviewData()],
                ['form' => $form]
            )
        );
        $content->setTemplate('pages/application/overview');

        return $this->render($content);
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

    /**
     * @return array multiItems for the readonly part of the view
     */
    protected function getOverviewData()
    {
        $id = $this->getIdentifier();
        $service = $this->getServiceLocator()->get('Entity\Application');
        $overviewData = [];

        $typeOfLicenceData = $service->getTypeOfLicenceData($id);

        // get interim status for GV apps
        if ($typeOfLicenceData['goodsOrPsv'] == Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {

            $applicationData = $service->getDataForInterim($id);

            $url = $this->getServiceLocator()->get('Helper\Url')
                ->fromRoute('lva-'.$this->lva.'/interim', [], [], true);

            if (
                isset($applicationData['interimStatus']['id'])
                && !empty($applicationData['interimStatus']['id'])
            ) {
                $interimStatus = sprintf(
                    '%s (<a href="%s">Interim details</a>)',
                    $applicationData['interimStatus']['description'],
                    $url
                );
            } else {
                $interimStatus = sprintf('None (<a href="%s">add interim</a>)', $url);
            }

            $overviewData[] =  [
                [
                    'label' => 'Interim status',
                    'value' => $interimStatus,
                    'noEscape' => true,
                ],
            ];
        }

        return $overviewData;
    }
}
