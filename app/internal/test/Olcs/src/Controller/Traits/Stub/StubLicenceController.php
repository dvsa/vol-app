<?php

/**
 * Stub Licence Controller
 */
namespace OlcsTest\Controller\Traits\Stub;

use Olcs\Controller\Traits\LicenceControllerTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Stub Licence Controller
 */
class StubLicenceController implements ServiceLocatorAwareInterface
{
    use LicenceControllerTrait,
        ServiceLocatorAwareTrait;

    // private $params;

    // public function doRender($view)
    // {
    //     return $this->render($view);
    // }

    // public function setParams($params)
    // {
    //     $this->params = $params;
    // }

    // public function params($name)
    // {
    //     return isset($this->params[$name]) ? $this->params[$name] : null;
    // }
}
