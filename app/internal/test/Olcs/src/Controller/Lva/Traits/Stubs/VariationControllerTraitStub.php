<?php

/**
 * Variation Controller Trait Stub
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\AbstractController;

/**
 * Variation Controller Trait Stub
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationControllerTraitStub extends AbstractController
{
    use VariationControllerTrait;

    protected $applicationId;
    protected $accessibleSections = [];

    public function setAccessibleSections($accessibleSections)
    {
        $this->accessibleSections = $accessibleSections;
    }

    public function getAccessibleSections($keysOnly = true)
    {
        return $this->accessibleSections;
    }

    public function setApplicationId($id)
    {
        $this->applicationId = $id;
    }

    public function getApplicationId()
    {
        return $this->applicationId;
    }

    public function callGetSectionsForView()
    {
        return $this->getSectionsForView();
    }

    public function isPsv()
    {
        return true;
    }
}
