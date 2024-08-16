<?php

/**
 * Context Provider Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document;

/**
 * Context Provider Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ContextProviderInterface
{
    /**
     * @return string
     */
    public function getContextValue();
}
