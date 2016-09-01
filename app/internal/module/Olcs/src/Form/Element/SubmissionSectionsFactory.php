<?php

namespace Olcs\Form\Element;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionSectionsFactory
 * @package Olcs\Form\Element
 */
class SubmissionSectionsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $formElementManager
     * @return SubmissionSections
     */
    public function createService(ServiceLocatorInterface $formElementManager)
    {
        /** @var \Zend\Form\FormElementManager $formElementManager */
        $serviceLocator = $formElementManager->getServiceLocator();

        $element = new SubmissionSections();

        // set up TM ID to trigger additional TM sections when generating element
        $transportManagerElement = $formElementManager->get('Hidden');

        $case = $this->getCase($serviceLocator);

        if (!empty($case['transportManager']['id'])) {
            $transportManagerElement->setValue($case['transportManager']['id']);
        }
        $element->setTransportManager($transportManagerElement);

        /** @var \Common\Form\Element\DynamicSelect $submissionType */
        $submissionType = $formElementManager->get('DynamicSelect');
        $submissionType->setOptions(
            [
                'label' => 'Submission type',
                'category' => 'submission_type',
                'empty_option' => 'Please select',
                'disable_in_array_validator' => false,
                'help-block' => 'Please select a submission type'
            ]
        );
        $element->setSubmissionType($submissionType);

        /** @var \Common\Form\Element\Button $submissionTypeSubmit */
        $submissionTypeSubmit = $formElementManager->get('Submit');
        $submissionTypeSubmit->setOptions(
            [
                'label' => 'Select type',
                'label_attributes' => ['type' => 'submit', 'class' => 'col-sm-2'],
                'column-size' => 'sm-10',
            ]
        );

        $element->setSubmissionTypeSubmit($submissionTypeSubmit);

        /** @var \Common\Form\Element\SubmissionSections $submissionSections */
        $sections = $formElementManager->get('DynamicMultiCheckbox');
        $sectionOptions = [
            'label' => 'Sections',
            'category' => 'submission_section',
            'disable_in_array_validator' => false,
            'help-block' => 'Please choose your submission sections'
        ];

        $sections->setOptions($sectionOptions);
        $element->setSections($sections);

        return $element;
    }

    /**
     * Method to extract the case in order to get the transport manager and set it's id value as hidden field
     *
     * @param $serviceLocator
     * @return array $case
     */
    private function getCase($serviceLocator)
    {
        $cpm = $serviceLocator->get('ControllerPluginManager');
        $params = $cpm->get('params');
        $caseId = $params->fromRoute('case');
        $caseService = $serviceLocator->get('DataServiceManager')->get('Olcs\Service\Data\Cases');
        $case = $caseService->fetchData($caseId);

        return $case;
    }
}
