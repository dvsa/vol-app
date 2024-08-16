<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

/**
 * Toggle Required Interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface ToggleRequiredInterface
{
    /**
     * @return void
     */
    public function setToggleService(ToggleService $toggleService): void;

    /**
     * @return ToggleService
     */
    public function getToggleService(): ToggleService;
}
