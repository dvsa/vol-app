<?php
namespace Olcs\Form\View\Helper;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormElementDelegatorFactory implements DelegatorFactoryInterface
{
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /** @var \Zend\Form\View\Helper\FormElement $viewHelper */
        $viewHelper = call_user_func($callback);

        $viewHelper->addClass('\Olcs\Form\Element\SubmissionSections', 'formSubmissionSections');

        return $viewHelper;
    }
}
