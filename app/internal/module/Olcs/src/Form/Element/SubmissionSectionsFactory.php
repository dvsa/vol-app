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

        $service = new SubmissionSections();

        /** @var \Common\Form\Element\DynamicSelect $submissionType */
        $submissionType = $formElementManager->get('DynamicSelect');

        $options = [
            'label' => 'Submission type',
            'category' => 'submission_type',
            'empty_option' => 'Please select',
            'disable_in_array_validator' => false,
            'help-block' => 'Please select a submission type'
        ];

        $submissionType->setOptions($options);

        $submissionType->setName('submission_type');

        $service->setSubmissionType($submissionType);

        /** @var \Common\Form\Element\SubmissionSections $submissionSections */
        $submissionSections = $formElementManager->get('DynamicMultiCheckbox');

        $submissionSectionsOptions = [
            'label' => 'Sections',
            'category' => 'submission_section',
            'disable_in_array_validator' => false,
            'help-block' => 'Please choose your submission sections'
        ];

        $submissionSections->setOptions($submissionSectionsOptions);
        $submissionSections->setName('submission_sections');

        $service->setSubmissionSections($submissionSections);
        return $service;
    }
}
