<?php

namespace Olcs\Controller\IrhpPermits;

use Zend\Mvc\MvcEvent;

/**
 * Show Irhp Application Navigation trait
 */
trait ShowIrhpApplicationNavigationTrait
{
    public function onDispatch(MvcEvent $e)
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        $navigation->findOneBy('id', 'licence_irhp_applications')->setVisible(1);

        return parent::onDispatch($e);
    }
}
