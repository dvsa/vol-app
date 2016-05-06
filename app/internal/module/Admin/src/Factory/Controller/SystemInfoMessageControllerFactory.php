<?php

namespace Admin\Factory\Controller;

use Admin\Controller\SystemInfoMessageController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for SystemInfoMessageController
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class SystemInfoMessageControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var \Zend\View\HelperPluginManager $sl */
        /** @var ServiceLocatorInterface $sm */
        $sm = $sl->getServiceLocator();

        /** @var \Common\Service\Helper\FormHelperService $formHelper */
        $formHelper = $sm->get('Helper\Form');

        return new SystemInfoMessageController($formHelper);
    }
}
