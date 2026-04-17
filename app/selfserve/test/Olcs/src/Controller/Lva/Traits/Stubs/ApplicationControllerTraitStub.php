<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Application Controller Trait Stub
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationControllerTraitStub extends AbstractActionController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';

    public function callRender(string|\Laminas\View\Model\ViewModel $title, ?\Common\Form\Form $form = null, array $variables = []): \Laminas\View\Model\ViewModel
    {
        return $this->render($title, $form, $variables);
    }
}
