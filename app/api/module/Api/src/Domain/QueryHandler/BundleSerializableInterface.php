<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler;

/**
 * Bundle Serializable Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface BundleSerializableInterface
{
    /**
     * Recursively serialize objects based on the bundle
     */
    public function serialize(array $bundle = []);
}
