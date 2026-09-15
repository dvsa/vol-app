<?php

namespace Common\Service\Data\Interfaces;

/**
 * Interface Creatable
 * @package Common\Service\Data\Interfaces
 */
interface Creatable
{
    /**
     * Returns an array of defaults for this item
     *
     * @return array
     */
    public function getNew();
}
