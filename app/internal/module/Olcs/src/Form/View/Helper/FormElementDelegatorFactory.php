<?php
namespace Olcs\Form\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class FormElementDelegatorFactory
 *
 * @package Olcs\Form\View\Helper
 */
class FormElementDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, callable $callback, array $options = null)
    {
        /** @var \Laminas\Form\View\Helper\FormElement $viewHelper */
        $viewHelper = call_user_func($callback);

        $viewHelper->addClass('\Olcs\Form\Element\SubmissionSections', 'formSubmissionSections');

        return $viewHelper;
    }

    /**
     * {@inheritdoc}
     * @todo OLCS-28149
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        return $this($serviceLocator, $requestedName, $callback);
    }
}
