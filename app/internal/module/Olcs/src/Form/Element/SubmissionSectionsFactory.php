<?php

namespace Olcs\Form\Element;

use Common\Form\Element\Button;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SubmissionSectionsFactory implements FactoryInterface
{
    /**
     * Method to extract the case in order to get the transport manager and set it's id value as hidden field
     *
     * @return array
     */
    private function getCase(ContainerInterface $serviceLocator)
    {
        $cpm = $serviceLocator->get('ControllerPluginManager');
        $params = $cpm->get('params');
        $caseId = $params->fromRoute('case');
        $caseService = $serviceLocator->get('DataServiceManager')->get(\Olcs\Service\Data\Cases::class);
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
        /** @var Button $submissionTypeSubmit */
        $submissionTypeSubmit = $formElementManager->get('Submit');
        $submissionTypeSubmit->setOptions(
            [
                'label' => 'Select type',
                'label_attributes' => ['type' => 'submit'],
                'column-size' => 'sm-10',
            ]
        );
        $element->setSubmissionTypeSubmit($submissionTypeSubmit);
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
