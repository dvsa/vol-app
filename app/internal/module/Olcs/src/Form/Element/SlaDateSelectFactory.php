<?php

namespace Olcs\Form\Element;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SlaDateSelectFactory
 *
 * @package Olcs\Form\Element\SlaDateSelect
 */
class SlaDateSelectFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $formElementManager
     * @return SlaDateSelect
     */
    public function createService(ServiceLocatorInterface $formElementManager)
    {
        /** @var \Zend\Form\FormElementManager $formElementManager */
        $serviceLocator = $formElementManager->getServiceLocator();

        $element = new SlaDateSelect();
        $element->setSlaService($serviceLocator->get('Common\Service\Data\Sla'));

        return $element;
    }
}
