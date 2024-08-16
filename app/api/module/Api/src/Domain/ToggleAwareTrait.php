<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

/**
 * Toggle Aware Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait ToggleAwareTrait
{
    /**
     * @var ToggleService
     */
    private $toggleService;

    /**
     * @return void
     */
    public function setToggleService(ToggleService $toggleService): void
    {
        $this->toggleService = $toggleService;
    }

    /**
     * @return ToggleService
     */
    public function getToggleService(): ToggleService
    {
        return $this->toggleService;
    }
}
