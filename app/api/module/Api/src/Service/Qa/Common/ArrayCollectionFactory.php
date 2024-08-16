<?php

namespace Dvsa\Olcs\Api\Service\Qa\Common;

use Doctrine\Common\Collections\ArrayCollection;

class ArrayCollectionFactory
{
    /**
     * Get an ArrayCollection instance
     *
     * @return ArrayCollection
     */
    public function create()
    {
        return new ArrayCollection();
    }
}
