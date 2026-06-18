<?php

/**
 * Ordered Query Interface
 */

namespace Dvsa\Olcs\Transfer\Query;

/**
 * Ordered Query Interface
 */
interface OrderedQueryInterface
{
    /**
     * @return string
     */
    public function getSort();

    /**
     * @return string
     */
    public function getOrder();

    /**
     * @return bool
     */
    public function isSortWhitelisted();
}
