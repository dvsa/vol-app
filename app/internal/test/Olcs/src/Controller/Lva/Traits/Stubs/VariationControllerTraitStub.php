<?php

declare(strict_types=1);

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

    public function setAccessibleSections(mixed $accessibleSections): void
    {
        $this->accessibleSections = $accessibleSections;
    }

    #[\Override]
    public function getAccessibleSections($keysOnly = true): array
    {
        return $this->accessibleSections;
    }

    public function setApplicationId(mixed $id): void
    {
        $this->applicationId = $id;
    }

    public function getApplicationId(): mixed
    {
        return $this->applicationId;
    }

    public function callGetSectionsForView(): array
    {
        return $this->getSectionsForView();
    }

    public function isPsv(): bool
    {
        return true;
    }
}
