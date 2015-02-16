<?php

namespace Olcs\Form\Element;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SlaDateTimeSelectFactory
 *
 * @package Olcs\Form\Element\SlaDateTimeSelect
 */
class SlaDateTimeSelectFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $formElementManager
     * @return SlaDateTimeSelect
     */
    public function createService(ServiceLocatorInterface $formElementManager)
    {
        /** @var \Zend\Form\FormElementManager $formElementManager */
        $serviceLocator = $formElementManager->getServiceLocator();

        $element = new SlaDateTimeSelect();
        $element->setSlaService($serviceLocator->get('Common\Service\Data\Sla'));

        return $element;
    }
}
