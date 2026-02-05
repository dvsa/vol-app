<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler;

use Doctrine\Common\Collections\Collection;

/**
 * Result List
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResultList extends Result
{
    /**
     * @var BundleSerializableInterface[]
     */
    private $objects;

    public function __construct($objects, array $bundle = [])
    {
        if ($objects instanceof Collection) {
            $objects = $objects->toArray();
        }

        $this->objects = $objects;
        $this->bundle = $bundle;
    }

    /**
     * Recursively serialize objects based on the bundle
     */
    #[\Override]
    public function serialize()
    {
        $list = [];

        foreach ($this->objects as $object) {
            $list[] = $object->serialize($this->bundle);
        }

        return $list;
    }
}
