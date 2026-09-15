<?php

/**
 * Paged Query Interface
 */

namespace Dvsa\Olcs\Transfer\Query;

/**
 * Paged Query Interface
 */
interface PagedQueryInterface
{
    /**
     * @return int
     */
    public function getPage();

    /**
     * @return int
     */
    public function getLimit();
}
