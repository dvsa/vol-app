<?php

namespace Olcs\Form\Element;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionSectionsFactory
 * @package Olcs\Form\Element
 */
class SubmissionSectionsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator Form element manager
     *
     * @return SubmissionSections
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SubmissionSections
    {
        return $this->__invoke($serviceLocator, SubmissionSections::class);
    }

    /**
     * Method to extract the case in order to get the transport manager and set it's id value as hidden field
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return array
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

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SubmissionSections
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SubmissionSections
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        $formElementManager = $container->get('FormElementManager');
        $element = new SubmissionSections();
        // set up TM ID to trigger additional TM sections when generating element
        $transportManagerElement = $formElementManager->get('Hidden');
        $case = $this->getCase($container);
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
                'label_attributes' => ['type' => 'submit'],
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
}
