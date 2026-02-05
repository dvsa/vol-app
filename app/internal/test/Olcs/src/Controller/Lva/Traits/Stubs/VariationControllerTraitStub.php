<?php

/**
 * Variation Controller Trait Stub
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\AbstractController;
use LmcRbacMvc\Service\AuthorizationService;

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

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected StringHelperService $stringHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    public function setAccessibleSections($accessibleSections)
    {
        $this->accessibleSections = $accessibleSections;
    }

    #[\Override]
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
