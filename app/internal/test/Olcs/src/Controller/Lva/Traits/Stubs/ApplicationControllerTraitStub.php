<?php

/**
 * Application Controller Trait Stub
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\AbstractController;

/**
 * Application Controller Trait Stub
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTraitStub extends AbstractController
{
    use ApplicationControllerTrait;

    public function callPreDispatch()
    {
        return $this->preDispatch();
    }

    public function callGetSectionsForView()
    {
        return $this->getSectionsForView();
    }
}
