<?php

/**
 * External Controller Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * External Controller Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ExternalControllerTraitStub extends AbstractActionController
{
    use ExternalControllerTrait;

    protected $lva = 'licence';

    public function callRender($title, $form = null, $variables = []): \Laminas\View\Model\ViewModel
    {
        return $this->render($title, $form, $variables);
    }
}
