<?php

/**
 * Stub Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Traits\Stub;

use Olcs\Controller\Traits\ApplicationControllerTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Stub Application Controller
 *
 * @NOTE this class exists so we can cleanly test the ApplicationControllerTraits protected methods
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StubApplicationController implements ServiceLocatorAwareInterface
{
    use ApplicationControllerTrait,
        ServiceLocatorAwareTrait;

    private $params;

    public function doRender($view)
    {
        return $this->render($view);
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function params($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }
}
