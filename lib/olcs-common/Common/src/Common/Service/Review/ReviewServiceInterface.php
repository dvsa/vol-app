<?php

/**
 * Review Service Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Review;

/**
 * Review Service Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ReviewServiceInterface
{
    /**
     * Format the readonly config from the given data
     *
     * @return array
     */
    public function getConfigFromData(array $data = []);
}
