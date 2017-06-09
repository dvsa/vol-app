<?php

namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Application Controller Trait Stub
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationControllerTraitStub extends AbstractActionController
{
    protected $lva = 'application';

    use ApplicationControllerTrait;

    public function callRender($title, $form = null, $variables = array())
    {
        return $this->render($title, $form, $variables);
    }
}
