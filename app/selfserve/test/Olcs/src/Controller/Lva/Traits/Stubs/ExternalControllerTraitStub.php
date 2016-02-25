<?php

/**
 * External Controller Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * External Controller Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ExternalControllerTraitStub extends AbstractActionController
{
    protected $lva = 'licence';

    use ExternalControllerTrait;

    public function callRender($title, $form = null, $variables = array())
    {
        return $this->render($title, $form, $variables);
    }
}
