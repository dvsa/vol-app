<?php
namespace Olcs\Form\View\Helper;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FormElementDelegatorFactory
 *
 * @package Olcs\Form\View\Helper
 */
class FormElementDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Declare view helper delegator for submissionSections
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return mixed|\Zend\Form\View\Helper\FormElement
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /** @var \Zend\Form\View\Helper\FormElement $viewHelper */
        $viewHelper = call_user_func($callback);

        $viewHelper->addClass('\Olcs\Form\Element\SubmissionSections', 'formSubmissionSections');

        return $viewHelper;
    }
}
